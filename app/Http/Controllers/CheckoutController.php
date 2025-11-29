<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        $total = $this->orderService->calculateTotal($cartItems);
        $depositAmount = $this->paymentService->calculateDeposit(
            (object)['total_amount' => $total],
            $this->paymentService->getDefaultDepositPercentage()
        );

        $addresses = Auth::user()->addresses;

        return view('checkout.show', compact('cartItems', 'total', 'depositAmount', 'addresses'));
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
        $request->validate([
            'payment_type' => 'required|in:full,deposit',
            'payment_method' => 'required|string',
            // Allow either an existing shipping_address_id or full address fields
            'shipping_address_id' => 'nullable|exists:addresses,id',

            // Address fields required when shipping_address_id is not provided
            'first_name' => 'required_without:shipping_address_id|string|max:100',
            'last_name' => 'required_without:shipping_address_id|string|max:100',
            'company' => 'nullable|string|max:255',
            'address_line_1' => 'required_without:shipping_address_id|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required_without:shipping_address_id|string|max:100',
            'state_province' => 'required_without:shipping_address_id|string|max:100',
            'postal_code' => 'required_without:shipping_address_id|string|max:20',
            'country' => 'required_without:shipping_address_id|string|max:100',
            'save_address' => 'sometimes|boolean',
        ]);

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
            // Build snapshot from provided fields
            $addressData = [
                'type' => 'shipping',
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
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
                $created = Address::create(array_merge($addressData, ['user_id' => Auth::id()]));
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
            $cartItems->push([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'size_label' => $item['size_label'] ?? null,
            ]);
        }

        return $cartItems;
    }
}
