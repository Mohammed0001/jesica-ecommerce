<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\SiteSetting;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GuestCheckoutController extends Controller
{
    protected OrderService $orderService;
    protected PaymentService $paymentService;

    public function __construct(OrderService $orderService, PaymentService $paymentService)
    {
        // No auth middleware — guest checkout is public
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
    }

    /**
     * Show the guest checkout page
     */
    public function show()
    {
        $cartItems = $this->getCartItems();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $errors = $this->orderService->validateCartItems($cartItems);
        if (!empty($errors)) {
            return redirect()->route('cart.index')->with('error', implode(', ', $errors));
        }

        // Calculate totals
        $displaySubtotal = collect($cartItems)->sum(function ($it) {
            return (float) (data_get($it, 'product.display_subtotal') ?? 0);
        });

        // Default delivery: Egypt / Giza
        $deliveryFee    = Region::getDeliveryFeeForLocation(null, null, 'Egypt');
        $deliveryThreshold = (float) SiteSetting::get('delivery_threshold', 200);
        $taxPercentage     = (float) SiteSetting::get('tax_percentage', 14);
        $serviceFeePercentage = (float) SiteSetting::get('service_fee_percentage', 0);

        $appliedPromo  = session()->get('applied_promo', null);
        $discountAmount = 0;
        if ($appliedPromo) {
            $discountAmount = $appliedPromo['type'] === 'percentage'
                ? ($displaySubtotal * ($appliedPromo['value'] / 100))
                : $appliedPromo['value'];
        }

        $finalTotal  = max(0, $displaySubtotal - $discountAmount);
        $shipping    = $finalTotal >= $deliveryThreshold ? 0 : $deliveryFee;
        $serviceFee  = round($finalTotal * ($serviceFeePercentage / 100), 2);
        $tax         = round(($finalTotal + $serviceFee + $shipping) * ($taxPercentage / 100), 2);
        $total       = round(max(0, $finalTotal + $serviceFee + $shipping + $tax), 2);

        $depositAmount = $this->paymentService->calculateDeposit(
            (object)['total_amount' => $total],
            $this->paymentService->getDefaultDepositPercentage()
        );

        $cairoDistricts = Region::cairoDistricts();
        $governorates   = Region::governorates();

        $deliveryFeeData = [
            'threshold'     => $deliveryThreshold,
            'taxPercentage' => $taxPercentage,
            'servicePct'    => $serviceFeePercentage,
        ];

        return view('checkout.guest', compact(
            'cartItems', 'total', 'depositAmount',
            'displaySubtotal', 'shipping', 'serviceFee', 'tax',
            'discountAmount', 'finalTotal', 'deliveryFeeData',
            'cairoDistricts', 'governorates'
        ));
    }

    /**
     * Process guest checkout
     */
    public function process(Request $request)
    {
        // Collapse any accidentally duplicated array inputs
        $collapseFields = ['first_name','last_name','email','phone','company','address_line_1','address_line_2','city','state_province','postal_code','country'];
        foreach ($collapseFields as $f) {
            if ($request->has($f) && is_array($request->input($f))) {
                $val = $request->input($f);
                $request->merge([$f => is_array($val) ? ($val[0] ?? null) : $val]);
            }
        }

        $request->validate([
            // Contact
            'email'          => 'required|email|max:255',
            'news_offers'    => 'sometimes|boolean',
            // Delivery
            'first_name'     => 'required|string|max:100',
            'last_name'      => 'required|string|max:100',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city'           => 'required|string|max:100',
            'state_province' => 'required|string|max:100',
            'postal_code'    => 'nullable|string|max:20',
            'country'        => 'required|string|max:100',
            'phone'          => 'required|string|max:30',
            // Payment
            'payment_type'   => 'required|in:full,deposit',
            'payment_method' => 'required|string',
        ]);

        $cartItems = $this->getCartItems();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        // Build address snapshot from form data
        $addressSnapshot = [
            'type'           => 'shipping',
            'first_name'     => $request->first_name,
            'last_name'      => $request->last_name,
            'company'        => $request->company,
            'address_line_1' => $request->address_line_1,
            'address_line_2' => $request->address_line_2,
            'city'           => $request->city,
            'state_province' => $request->state_province,
            'postal_code'    => $request->postal_code,
            'country'        => $request->country,
            'phone'          => $request->phone,
            'email'          => $request->email,
        ];

        // Resolve city/district/country for delivery fee
        $city     = $request->city;
        $district = $request->state_province;
        $country  = $request->country;

        // If country is Egypt and city is Cairo, district holds the cairo area
        if ($country === 'Egypt' && strtolower($city) === 'cairo') {
            $city     = 'Cairo';
            $district = $request->state_province;
        }

        $guestData = [
            'email' => $request->email,
            'name'  => trim($request->first_name . ' ' . $request->last_name),
            'phone' => $request->phone,
        ];

        DB::beginTransaction();

        try {
            // Create order with null user (guest)
            $order = $this->orderService->createOrder(
                null,
                $cartItems,
                null,
                $city,
                $district,
                $country,
                $guestData
            );

            $order->update(['shipping_address_snapshot' => $addressSnapshot]);

            // Process payment
            if ($request->payment_type === 'full') {
                $result = $this->paymentService->processFullPayment($order, $request->payment_method);
            } else {
                $depositAmount = $this->paymentService->calculateDeposit(
                    $order,
                    $this->paymentService->getDefaultDepositPercentage()
                );
                $result = $this->paymentService->processDeposit($order, $depositAmount, $request->payment_method);
            }

            if ($result['success']) {
                $status = $result['status'] ?? 'succeeded';

                if ($status === 'succeeded') {
                    $this->orderService->decrementStock($order);
                    session()->forget('cart');
                    session()->forget('applied_promo');

                    DB::commit();

                    return redirect()->route('guest.checkout.confirmation', ['order' => $order->order_number])
                        ->with('success', $result['message'] ?? 'Order placed successfully!');
                }

                DB::commit();

                if (!empty($result['meta']['iframe_url'])) {
                    $iframeUrl = $result['meta']['iframe_url'];
                    return view('checkout.iframe', compact('order', 'iframeUrl'));
                }

                return redirect()->route('guest.checkout.confirmation', ['order' => $order->order_number])
                    ->with('info', $result['message'] ?? 'Payment pending');
            } else {
                DB::rollBack();
                return back()->with('error', $result['message'])->withInput();
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Guest checkout failed: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'An error occurred during checkout. Please try again.')->withInput();
        }
    }

    /**
     * Order confirmation page for guests
     */
    public function confirmation(string $orderNumber)
    {
        $order = \App\Models\Order::where('order_number', $orderNumber)
            ->whereNull('user_id')
            ->firstOrFail();

        return view('checkout.guest_confirmation', compact('order'));
    }

    /**
     * AJAX: delivery fee lookup (mirrors CheckoutController)
     */
    public function getDeliveryFee(Request $request)
    {
        $city     = $request->input('city', '');
        $district = $request->input('district', '');
        $country  = $request->input('country', 'Egypt');
        $threshold = (float) SiteSetting::get('delivery_threshold', 200);
        $fee = Region::getDeliveryFeeForLocation($city ?: null, $district ?: null, $country ?: null);

        return response()->json([
            'city'      => $city,
            'district'  => $district,
            'country'   => $country,
            'fee'       => $fee,
            'threshold' => $threshold,
        ]);
    }

    /**
     * Get cart items from session (same logic as CheckoutController)
     */
    private function getCartItems()
    {
        $cart      = session()->get('cart', []);
        $cartItems = collect();

        foreach ($cart as $item) {
            $product = \App\Models\Product::with('images')->find($item['product_id']);

            $productData = null;
            if ($product) {
                $mainImage    = $product->main_image;
                $displayPrice = $product->convertToCurrency(session('currency', $product->currency));
                $productData  = [
                    'id'                => $product->id,
                    'sku'               => $product->sku,
                    'title'             => $product->title,
                    'description'       => $product->description,
                    'price'             => (float) $product->price,
                    'currency'          => $product->currency,
                    'is_one_of_a_kind'  => (bool) $product->is_one_of_a_kind,
                    'quantity_available'=> $product->quantity,
                    'main_image_url'    => $mainImage?->url,
                    'display_price'     => $displayPrice,
                    'display_subtotal'  => round($displayPrice * $item['quantity'], 2),
                ];
            }

            $cartItems->push([
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'size_label' => $item['size_label'] ?? null,
                'product'    => $productData,
            ]);
        }

        return $cartItems;
    }
}
