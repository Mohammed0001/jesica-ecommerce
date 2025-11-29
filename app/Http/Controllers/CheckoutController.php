<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    protected OrderService $orderService;
    protected PaymentService $paymentService;

    public function __construct(OrderService $orderService, PaymentService $paymentService)
    {
        $this->middleware('auth');
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
    }

    /**
     * Show checkout page
     */
    public function show()
    {
        $cartItems = $this->getCartItems();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.show')->with('error', 'Your cart is empty.');
        }

        // Validate cart items
        $errors = $this->orderService->validateCartItems($cartItems);
        if (!empty($errors)) {
            return redirect()->route('cart.show')->with('error', implode(', ', $errors));
        }

        // Calculate totals in display currency for checkout view
        $subtotalOriginal = $this->orderService->calculateTotal($cartItems);
        // Use display_subtotal from product snapshot when available
        $displaySubtotal = collect($cartItems)->sum(function ($it) {
            return (float) (data_get($it, 'product.display_subtotal') ?? (data_get($it, 'display_subtotal') ?? 0));
        });

        // Use SiteSetting for fees like in CartController
        $deliveryFee = (float) \App\Models\SiteSetting::get('delivery_fee', 15);
        $deliveryThreshold = (float) \App\Models\SiteSetting::get('delivery_threshold', 200);
        $taxPercentage = (float) \App\Models\SiteSetting::get('tax_percentage', 14);
        $serviceFeePercentage = (float) \App\Models\SiteSetting::get('service_fee_percentage', 0);

        $appliedPromo = session()->get('applied_promo', null);
        $discountAmount = 0;
        if ($appliedPromo) {
            if ($appliedPromo['type'] === 'percentage') {
                $discountAmount = ($displaySubtotal * ($appliedPromo['value'] / 100));
            } else {
                $discountAmount = $appliedPromo['value'];
            }
        }

        $finalTotal = max(0, $displaySubtotal - $discountAmount);
        $shipping = $finalTotal >= $deliveryThreshold ? 0 : $deliveryFee;
        $serviceFee = round($finalTotal * ($serviceFeePercentage / 100), 2);
        $tax = round(($finalTotal + $serviceFee + $shipping) * ($taxPercentage / 100), 2);
        $total = round(max(0, $finalTotal + $serviceFee + $shipping + $tax), 2);

        $depositAmount = $this->paymentService->calculateDeposit(
            (object)['total_amount' => $total],
            $this->paymentService->getDefaultDepositPercentage()
        );

        $addresses = Auth::user()->addresses;

        return view('checkout.show', compact('cartItems', 'total', 'depositAmount', 'addresses', 'displaySubtotal', 'shipping', 'serviceFee', 'tax', 'discountAmount', 'finalTotal'));
    }

    /**
     * Backwards-compatible alias for show()
     */
    public function index()
    {
        return $this->show();
    }

    /**
     * Process checkout
     */
    public function process(Request $request)
    {
        // Normalize common address fields in case the client submitted arrays (e.g., duplicate inputs)
        $addressFields = ['company','address_line_1','address_line_2','city','state_province','postal_code','country','save_address','is_default'];
        foreach ($addressFields as $f) {
            if ($request->has($f) && is_array($request->input($f))) {
                // Collapse to first element to allow string validation to proceed
                $val = $request->input($f);
                $request->merge([$f => is_array($val) ? ($val[0] ?? null) : $val]);
            }
        }

        // Build conditional rules: require address fields only when shipping_address_id not provided
        $rules = [
            'payment_type' => 'required|in:full,deposit',
            'payment_method' => 'required|string',
            // Allow either an existing shipping_address_id or full address fields
            'shipping_address_id' => 'nullable|exists:addresses,id',
            'save_address' => 'sometimes|boolean',
        ];

        if ($request->filled('shipping_address_id')) {
            // If user selected an existing address, address inputs are optional
            $rules = array_merge($rules, [
                'company' => 'nullable|string|max:255',
                'address_line_1' => 'nullable|string|max:255',
                'address_line_2' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:100',
                'state_province' => 'nullable|string|max:100',
                'postal_code' => 'nullable|string|max:20',
                'country' => 'nullable|string|max:100',
            ]);
        } else {
            // Require address fields when no existing address id provided
            $rules = array_merge($rules, [
                'company' => 'nullable|string|max:255',
                'address_line_1' => 'required|string|max:255',
                'address_line_2' => 'nullable|string|max:255',
                'city' => 'required|string|max:100',
                'state_province' => 'required|string|max:100',
                'postal_code' => 'required|string|max:20',
                'country' => 'required|string|max:100',
            ]);
        }

        $request->validate($rules);

        $cartItems = $this->getCartItems();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.show')->with('error', 'Your cart is empty.');
        }

        // Determine shipping address: either existing (belongs to user) or inline data
        $shippingAddressId = null;
        $addressSnapshot = null;

        if ($request->filled('shipping_address_id')) {
            // Validate that the address belongs to the user
            $address = Address::where('id', $request->shipping_address_id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$address) {
                return back()->with('error', 'Invalid shipping address.');
            }

            $shippingAddressId = $address->id;
            $addressSnapshot = $address->toArray();
        } else {
            // Build snapshot from provided fields (no first/last name fields)
            $addressData = [
                'type' => 'shipping',
                'company' => $request->company,
                'address_line_1' => $request->address_line_1,
                'address_line_2' => $request->address_line_2,
                'city' => $request->city,
                'state_province' => $request->state_province,
                'postal_code' => $request->postal_code,
                'country' => $request->country,
                'is_default' => false,
            ];

            // Optionally save address to user's profile
            if ($request->boolean('save_address')) {
                // If marked as default, clear other defaults first
                if ($request->boolean('is_default')) {
                    Address::where('user_id', Auth::id())->update(['is_default' => false]);
                }

                $created = Address::create(array_merge($addressData, ['user_id' => Auth::id(), 'is_default' => $request->boolean('is_default')]));
                $shippingAddressId = $created->id;
                $addressSnapshot = $created->toArray();
            } else {
                $addressSnapshot = $addressData;
            }
        }

        DB::beginTransaction();

        try {
            // Create order using the resolved shipping address id (may be null)
            $order = $this->orderService->createOrder(
                Auth::user(),
                $cartItems,
                $shippingAddressId
            );

            // Store address snapshot (either saved model or inline data)
            $order->update([
                'shipping_address_snapshot' => $addressSnapshot
            ]);

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
                    // Final success: decrement stock and clear cart
                    $this->orderService->decrementStock($order);
                    session()->forget('cart');

                    DB::commit();

                    return redirect()->route('orders.show', $order)
                        ->with('success', $result['message'] ?? 'Payment completed');
                }

                // Pending (redirect) - commit order but do not decrement stock yet
                DB::commit();

                // If gateway returned an iframe/redirect URL, send the user there
                if (!empty($result['meta']['iframe_url'])) {
                    $iframeUrl = $result['meta']['iframe_url'];
                    return view('checkout.iframe', compact('order', 'iframeUrl'));
                }

                return redirect()->route('orders.show', $order)
                    ->with('info', $result['message'] ?? 'Payment pending');
            } else {
                DB::rollBack();
                return back()->with('error', $result['message']);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            // Log exception for debugging
            Log::error('Checkout process failed: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'An error occurred during checkout. Please try again.');
        }
    }

    /**
     * Get cart items from session
     */
    private function getCartItems()
    {
        $cart = session()->get('cart', []);
        $cartItems = collect();

        foreach ($cart as $item) {
            $product = \App\Models\Product::with('images')->find($item['product_id']);

            $productData = null;
            if ($product) {
                $mainImage = $product->main_image;
                $displayPrice = $product->convertToCurrency(session('currency', $product->currency));
                $productData = [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'title' => $product->title,
                    'description' => $product->description,
                    'price' => (float) $product->price,
                    'currency' => $product->currency,
                    'is_one_of_a_kind' => (bool) $product->is_one_of_a_kind,
                    'quantity_available' => $product->quantity,
                    'main_image_url' => $mainImage?->url,
                    'display_price' => $displayPrice,
                    'display_subtotal' => round($displayPrice * $item['quantity'], 2),
                ];
            }

            $cartItems->push([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'size_label' => $item['size_label'] ?? null,
                'product' => $productData,
            ]);
        }

        return $cartItems;
    }
}
