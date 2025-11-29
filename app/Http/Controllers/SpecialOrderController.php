<?php

namespace App\Http\Controllers;

use App\Models\SpecialOrder;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SpecialOrderController extends Controller
{
    /**
     * List authenticated user's special orders
     */
    public function index()
    {
        $orders = SpecialOrder::where('user_id', Auth::id())
            ->latest()
            ->paginate(12);

        return view('special-orders.index', compact('orders'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        // Provide a lightweight product list for optional association
        $products = Product::visible()->select(['id', 'name'])->limit(50)->get();

        return view('special-orders.create', compact('products'));
    }

    /**
     * Store a new special order
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'measurements' => 'nullable|string',
            'estimated_price' => 'nullable|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'desired_delivery_date' => 'nullable|date',
            'product_id' => 'nullable|exists:products,id',
            'message' => 'nullable|string',
        ]);

        $special = SpecialOrder::create([
            'user_id' => Auth::id(),
            'title' => $data['title'],
            'description' => $data['description'],
            'measurements' => $data['measurements'] ? json_decode($data['measurements'], true) ?? $data['measurements'] : null,
            'estimated_price' => $data['estimated_price'] ?? null,
            'deposit_amount' => $data['deposit_amount'] ?? null,
            'desired_delivery_date' => $data['desired_delivery_date'] ?? null,
            'product_id' => $data['product_id'] ?? null,
            'message' => $data['message'] ?? null,
            'status' => 'requested',
        ]);

        return redirect()->route('special-orders.show', $special)->with('success', 'Special order submitted successfully. We will contact you soon.');
    }

    /**
     * Show a user's special order
     */
    public function show(SpecialOrder $specialOrder)
    {
        // Only allow owner to view their special order
        if ($specialOrder->user_id !== Auth::id()) {
            abort(403);
        }

        return view('special-orders.show', ['order' => $specialOrder]);
    }
}
