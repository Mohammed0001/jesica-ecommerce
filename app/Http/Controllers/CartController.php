<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductSize;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display the cart
     */
    public function index()
    {
        $cartItems = $this->getCartItems();
        $total = $this->calculateTotal($cartItems);

        return view('cart.index', compact('cartItems', 'total'));
    }

    /**
     * Add item to cart
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'size_label' => 'nullable|string',
        ]);

        $product = Product::findOrFail($request->product_id);

        // Check if product is available
        if (!$product->visible || !$product->isAvailable()) {
            return back()->with('error', 'Product is not available.');
        }

        // Validate size for multi-size products
        if (!$product->is_one_of_a_kind && $request->size_label) {
            $productSize = $product->sizes()
                ->where('size_label', $request->size_label)
                ->first();

            if (!$productSize || $productSize->quantity < $request->quantity) {
                return back()->with('error', 'Selected size is not available in the requested quantity.');
            }
        }

        // Add to cart
        $cart = session()->get('cart', []);
        $cartKey = $product->id . '_' . ($request->size_label ?? 'one_size');

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += $request->quantity;
        } else {
            $cart[$cartKey] = [
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'size_label' => $request->size_label,
                'price' => $product->price,
                'title' => $product->title,
                'image' => $product->main_image?->url,
            ];
        }

        session()->put('cart', $cart);

        return back()->with('success', 'Product added to cart!');
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request)
    {
        $request->validate([
            'cart_key' => 'required|string',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = session()->get('cart', []);

        if (isset($cart[$request->cart_key])) {
            $cart[$request->cart_key]['quantity'] = $request->quantity;
            session()->put('cart', $cart);
        }

        return back()->with('success', 'Cart updated!');
    }

    /**
     * Remove item from cart
     */
    public function remove(Request $request)
    {
        $request->validate([
            'cart_key' => 'required|string',
        ]);

        $cart = session()->get('cart', []);

        if (isset($cart[$request->cart_key])) {
            unset($cart[$request->cart_key]);
            session()->put('cart', $cart);
        }

        return back()->with('success', 'Item removed from cart!');
    }

    /**
     * Clear the entire cart
     */
    public function clear()
    {
        session()->forget('cart');
        return back()->with('success', 'Cart cleared!');
    }

    /**
     * Get cart items with product details
     */
    private function getCartItems()
    {
        $cart = session()->get('cart', []);
        $cartItems = collect();

        foreach ($cart as $key => $item) {
            $product = Product::with(['images'])->find($item['product_id']);

            if ($product) {
                $cartItems->push([
                    'cart_key' => $key,
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'size_label' => $item['size_label'],
                    'price' => $product->price, // Use current price
                    'subtotal' => $product->price * $item['quantity'],
                ]);
            }
        }

        return $cartItems;
    }

    /**
     * Calculate total price
     */
    private function calculateTotal($cartItems)
    {
        return $cartItems->sum('subtotal');
    }

    /**
     * Get cart count for display
     */
    public function count()
    {
        $cart = session()->get('cart', []);
        $count = array_sum(array_column($cart, 'quantity'));

        return response()->json(['count' => $count]);
    }
}
