<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Region;
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

        // Debug: Log cart items
        \Log::info('Checkout - Cart Items Count:', ['count' => $cartItems->count(), 'items' => $cartItems->toArray()]);
        \Log::info('Checkout - Session Cart:', ['cart' => session()->get('cart', [])]);

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Your bag is empty, so there is nothing to check out. Add a piece and try again.');
        }

        // Re-check stock, sizes and colours at the moment of purchase so the
        // customer gets a specific reason rather than a generic failure.
        $cartErrors = $this->orderService->validateCartItems($cartItems);

        if (!empty($cartErrors)) {
            return redirect()->route('cart.index')
                ->with('error', 'Your bag needs attention before checkout: ' . implode(' ', $cartErrors));
        }

        // Get BOSTA cities for dropdown
        $bostaCities = \App\Models\BostaCity::dropOffAvailable()
            ->orderBy('name')
            ->get(['id', 'name', 'name_ar']);

        // Validate cart items
        $errors = $this->orderService->validateCartItems($cartItems);
        \Log::info('Checkout - Validation Errors:', ['errors' => $errors]);
        if (!empty($errors)) {
            return redirect()->route('cart.index')->with('error', implode(', ', $errors));
        }

        // Calculate totals in display currency for checkout view
        $subtotalOriginal = $this->orderService->calculateTotal($cartItems);
        // Use display_subtotal from product snapshot when available
        $displaySubtotal = collect($cartItems)->sum(function ($it) {
            return (float) (data_get($it, 'product.display_subtotal') ?? (data_get($it, 'display_subtotal') ?? 0));
        });

        // Resolve per-area delivery fee: use city from first saved address as the initial value
        $addresses = Auth::user()->addresses;
        $initialCity = null;
        $initialDistrict = null;
        $initialCountry = 'Egypt';
        if ($addresses->isNotEmpty()) {
            $initialCity = $addresses->first()->city ?? null;
            $initialDistrict = $addresses->first()->state_province ?? null;
            $initialCountry = $addresses->first()->country ?? 'Egypt';
        }
        $deliveryFee = Region::getDeliveryFeeForLocation($initialCity, $initialDistrict, $initialCountry);
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

        // Get BOSTA cities for dropdown
        $bostaCities = \App\Models\BostaCity::dropOffAvailable()
            ->orderBy('name')
            ->get(['id', 'name', 'name_ar']);

        // Pass delivery fee data for live JS update
        $deliveryFeeData = [
            'threshold'     => $deliveryThreshold,
            'taxPercentage' => $taxPercentage,
            'servicePct'    => $serviceFeePercentage,
        ];

        // For smart cascading address picker on checkout
        $cairoDistricts = Region::cairoDistricts();
        $governorates   = Region::governorates();

        return view('checkout.show', compact(
            'cartItems', 'total', 'depositAmount', 'addresses',
            'displaySubtotal', 'shipping', 'serviceFee', 'tax',
            'discountAmount', 'finalTotal', 'bostaCities', 'deliveryFeeData',
            'cairoDistricts', 'governorates'
        ));
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
                'state_province' => 'nullable|string|max:100|min:3',
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
                'state_province' => 'required|string|max:100|min:3',
                'postal_code' => 'required|string|max:20',
                'country' => 'required|string|max:100',
            ]);
        }

        $request->validate($rules);

        $cartItems = $this->getCartItems();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
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
                return back()->withInput()->with('error', 'That delivery address is not on your account any more. Pick another saved address or enter a new one.');
            }

            $shippingAddressId = $address->id;
            $addressSnapshot = $address->toArray();
            // Add user's phone to snapshot for shipping providers
            $addressSnapshot['phone'] = Auth::user()->phone;
        } else {
            // Build snapshot from provided fields (no first/last name fields)
            $addressData = [
                'type' => 'shipping',
                'company' => $request->company,
                'address_line_1' => $request->address_line_1,
                'address_line_2' => $request->address_line_2,
                'city' => $request->city,
                'state_province' => $request->state_province ?? $request->city, // Fallback to city
                'district' => $request->district, // Add district field for BOSTA
                'postal_code' => $request->postal_code,
                'country' => $request->country,
                'is_default' => false,
                'phone' => Auth::user()->phone, // Add user's phone for shipping
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

        // Extract city, district, and country
        $city = null;
        $district = null;
        $country = 'Egypt';
        
        if (!empty($addressSnapshot['city'])) {
            $city = $addressSnapshot['city'];
        } elseif ($request->filled('city')) {
            $city = $request->city;
        }

        if (!empty($addressSnapshot['state_province'])) {
            $district = $addressSnapshot['state_province'];
        } elseif (!empty($addressSnapshot['district'])) {
            $district = $addressSnapshot['district'];
        } elseif ($request->filled('state_province')) {
            $district = $request->state_province;
        }

        if (!empty($addressSnapshot['country'])) {
            $country = $addressSnapshot['country'];
        } elseif ($request->filled('country')) {
            $country = $request->country;
        }

        DB::beginTransaction();

        try {
            // Create order
            $order = $this->orderService->createOrder(
                Auth::user(),
                $cartItems,
                $shippingAddressId,
                $city,
                $district,
                $country
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

        } catch (\Throwable $e) {
            DB::rollBack();

            // A short reference lets support tie the customer's report to the
            // exact log line without exposing internals on screen.
            $reference = strtoupper(\Illuminate\Support\Str::random(8));

            Log::error('Checkout process failed: ' . $e->getMessage(), [
                'reference' => $reference,
                'user_id' => Auth::id(),
                'payment_method' => $request->input('payment_method'),
                'payment_type' => $request->input('payment_type'),
                'exception' => $e,
            ]);

            return back()->withInput()->with('error', sprintf(
                'We could not complete your order (%s). Your bag has been kept and you have not been charged. Quote reference %s if you contact us.',
                config('app.debug') ? $e->getMessage() : 'payment could not be processed',
                $reference
            ));
        }
    }

    /**
     * AJAX: return the delivery fee for a given city
     * GET /checkout/delivery-fee?city=Cairo
     */
    public function getDeliveryFee(Request $request)
    {
        $city = $request->input('city', '');
        $district = $request->input('district', '');
        $country = $request->input('country', 'Egypt');
        $deliveryThreshold = (float) \App\Models\SiteSetting::get('delivery_threshold', 200);
        $fee = Region::getDeliveryFeeForLocation($city ?: null, $district ?: null, $country ?: null);

        return response()->json([
            'city'      => $city,
            'district'  => $district,
            'country'   => $country,
            'fee'       => $fee,
            'threshold' => $deliveryThreshold,
        ]);
    }

    /**
     * Get cart items from session
     */
    private function getCartItems()
    {
        $cart = session()->get('cart', []);
        $cartItems = collect();

        if (empty($cart)) {
            return $cartItems;
        }

        // One query for the whole bag rather than one per line.
        $products = \App\Models\Product::with(['images', 'colors'])
            ->whereIn('id', collect($cart)->pluck('product_id')->unique()->all())
            ->get()
            ->keyBy('id');

        foreach ($cart as $item) {
            $product = $products->get($item['product_id']);

            $productData = null;
            if ($product) {
                $mainImage = $product->main_image;
                $displayPrice = $product->convertToCurrency(session('currency', $product->currency));
                $productData = [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'title' => $product->title,
                    'description' => $product->description,
                    // Sale-aware: this is what the customer is charged.
                    'price' => $product->effective_price,
                    'original_price' => (float) $product->price,
                    'is_on_sale' => $product->isOnSale(),
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
                'color_name' => $item['color_name'] ?? null,
                'product' => $productData,
            ]);
        }

        return $cartItems;
    }
}
