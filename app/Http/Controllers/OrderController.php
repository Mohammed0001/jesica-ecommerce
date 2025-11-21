<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->middleware('auth');
        $this->paymentService = $paymentService;
    }

    /**
     * Display user's orders
     */
    public function index()
    {
        $orders = Auth::user()->orders()
            ->with(['items.product', 'payments'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    /**
     * Show the specified order
     */
    public function show(Order $order)
    {
        // Check if order belongs to authenticated user
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load([
            'items.product.images',
            'payments' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'shippingAddress'
        ]);

        return view('orders.show', compact('order'));
    }

    /**
     * Capture remaining payment for an order
     */
    public function captureRemaining(Request $request, Order $order)
    {
        // Check if order belongs to authenticated user
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        // Check if order has remaining balance
        if ($order->remaining_balance <= 0) {
            return back()->with('error', 'No remaining balance to capture.');
        }

        $request->validate([
            'payment_method' => 'required|string',
        ]);

        $result = $this->paymentService->captureRemaining($order, $request->payment_method);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        } else {
            return back()->with('error', $result['message']);
        }
    }

    /**
     * Cancel an order
     */
    public function cancel(Order $order, Request $request)
    {
        // Ensure user owns the order
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        // Can only cancel pending orders
        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This order cannot be cancelled.'
            ]);
        }

        $order->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled successfully.'
        ]);
    }

    /**
     * Reorder items from a previous order
     */
    public function reorder(Order $order)
    {
        // Ensure user owns the order
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        // Add items to cart and redirect to checkout
        // This is a simplified implementation
        return redirect()->route('cart.index')->with('success', 'Items added to cart from order #' . $order->order_number);
    }

    /**
     * Download order invoice
     */
    public function invoice(Order $order)
    {
        // Ensure user owns the order
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        // For now, just return a view - you could generate a PDF here
        return view('orders.invoice', compact('order'));
    }
}
