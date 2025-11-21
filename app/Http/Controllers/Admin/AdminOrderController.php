<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SpecialOrder;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    /**
     * Display a listing of orders
     */
    public function index()
    {
        $orders = Order::with(['user', 'orderItems.product'])
            ->latest()
            ->paginate(20);

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Display the specified order
     */
    public function show(Order $order)
    {
        $order->load(['user', 'orderItems.product']);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Update the specified order
     */
    public function update(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            'notes' => 'nullable|string',
        ]);

        $order->update([
            'status' => $request->status,
            'admin_notes' => $request->notes,
        ]);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Order updated successfully.');
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $order->update(['status' => $request->status]);

        return redirect()->back()
            ->with('success', 'Order status updated successfully.');
    }

    /**
     * Display a listing of special orders
     */
    public function specialOrders()
    {
        $specialOrders = SpecialOrder::with(['user'])
            ->latest()
            ->paginate(20);

        return view('admin.special-orders.index', compact('specialOrders'));
    }

    /**
     * Display the specified special order
     */
    public function showSpecialOrder(SpecialOrder $specialOrder)
    {
        $specialOrder->load(['user']);

        return view('admin.special-orders.show', compact('specialOrder'));
    }

    /**
     * Update special order status
     */
    public function updateSpecialOrderStatus(Request $request, SpecialOrder $specialOrder)
    {
        $request->validate([
            'status' => 'required|in:pending,in_review,quoted,approved,in_production,completed,cancelled',
            'quoted_price' => 'nullable|numeric|min:0',
            'admin_notes' => 'nullable|string',
        ]);

        $data = ['status' => $request->status];

        if ($request->filled('quoted_price')) {
            $data['quoted_price'] = $request->quoted_price;
        }

        if ($request->filled('admin_notes')) {
            $data['admin_notes'] = $request->admin_notes;
        }

        $specialOrder->update($data);

        return redirect()->back()
            ->with('success', 'Special order updated successfully.');
    }
}
