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
     * Process checkout
     */
    public function process(Request $request)
    {
        $request->validate([
            'payment_type' => 'required|in:full,deposit',
            'payment_method' => 'required|string',
            'shipping_address_id' => 'required|exists:addresses,id',
        ]);

        $cartItems = $this->getCartItems();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.show')->with('error', 'Your cart is empty.');
        }

        // Validate that the address belongs to the user
        $address = Address::where('id', $request->shipping_address_id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$address) {
            return back()->with('error', 'Invalid shipping address.');
        }

        DB::beginTransaction();

        try {
            // Create order
            $order = $this->orderService->createOrder(
                Auth::user(),
                $cartItems,
                $request->shipping_address_id
            );

            // Store address snapshot
            $order->update([
                'shipping_address_snapshot' => $address->toArray()
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
                // Decrement stock
                $this->orderService->decrementStock($order);

                // Clear cart
                session()->forget('cart');

                DB::commit();

                return redirect()->route('orders.show', $order)
                    ->with('success', $result['message']);
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
