<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductSize;
use App\Models\PromoCode;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display the cart
     */
    public function index()
    {
    $cartItems = $this->getCartItems();
    $subtotal = $this->calculateTotal($cartItems); // original currency subtotal
    $displaySubtotal = collect($cartItems)->sum('display_subtotal'); // display currency subtotal

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

        // Site-configurable fees
        $deliveryFee = (float) \App\Models\SiteSetting::get('delivery_fee', 15);
        $deliveryThreshold = (float) \App\Models\SiteSetting::get('delivery_threshold', 200);
        $taxPercentage = (float) \App\Models\SiteSetting::get('tax_percentage', 14);
        $serviceFeePercentage = (float) \App\Models\SiteSetting::get('service_fee_percentage', 0);

        // Delivery calculation
        $shipping = $finalTotal >= $deliveryThreshold ? 0 : $deliveryFee;

        // Service fee applied as percentage of subtotal after discount
        $serviceFee = round($finalTotal * ($serviceFeePercentage / 100), 2);

        // Tax applied on (subtotal after discount + service + shipping)
        $tax = round(($finalTotal + $serviceFee + $shipping) * ($taxPercentage / 100), 2);

        $total = round(max(0, $finalTotal + $serviceFee + $shipping + $tax), 2);

        $hasDepositItems = collect($cartItems)->contains(function ($item) {
            return (bool) data_get($item, 'is_deposit');
        });

    return view('cart.index', compact('cartItems', 'subtotal', 'displaySubtotal', 'tax', 'total', 'appliedPromo', 'discountAmount', 'finalTotal', 'shipping', 'serviceFee', 'hasDepositItems'));
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
        if (!$product->getAttribute('visible') || !$product->isAvailable()) {
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

        // If it's an AJAX request, return JSON with success and updated cart count
        if ($request->wantsJson() || $request->ajax()) {
            $cartCount = array_sum(array_column($cart, 'quantity'));
            return response()->json([ 'success' => true, 'message' => 'Product added to cart!', 'cartCount' => $cartCount ]);
        }

        return back()->with('success', 'Product added to cart!');
    }

    /**
     * Add from product page via product route (uses product param)
     */
    public function addFromProductPage(Request $request, Product $product)
    {
        // Ensure product_id is set for compatibility and forward to add()
        $request->merge(['product_id' => $product->id]);
        return $this->add($request);
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

        if ($request->wantsJson() || $request->ajax()) {
            $cartCount = array_sum(array_column($cart, 'quantity'));
            return response()->json(['success' => true, 'cart_count' => $cartCount]);
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

        if ($request->wantsJson() || $request->ajax()) {
            $cartCount = array_sum(array_column($cart, 'quantity'));
            return response()->json(['success' => true, 'cart_count' => $cartCount]);
        }

        return back()->with('success', 'Item removed from cart!');
    }

    /**
     * Clear the entire cart
     */
    public function clear()
    {
        session()->forget('cart');
        session()->forget('applied_promo');
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }
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
                    'product_id' => $product->id,
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'size_label' => $item['size_label'],
                    'price' => $product->price, // original numeric price stored on product
                    'display_price' => $product->convertToCurrency(session('currency', 'EGP')),
                    'formatted_price' => $product->formatted_price,
                    'display_subtotal' => $product->convertToCurrency(session('currency', 'EGP')) * $item['quantity'],
                    'subtotal' => $product->price * $item['quantity'], // original currency subtotal
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

    /**
     * Apply promocode to the cart
     */
    public function applyPromo(Request $request)
    {
        $request->validate([
            'promo_code' => 'required|string',
        ]);

        $promo = PromoCode::where('code', $request->promo_code)->first();

        if (!$promo || !$promo->isUsable()) {
            return response()->json(['success' => false, 'message' => 'Invalid promo code'], 400);
        }

        $cartItems = $this->getCartItems();
        $total = $this->calculateTotal($cartItems);

        $discount = 0;
        if ($promo->type === 'percentage') {
            $discount = ($total * ($promo->value / 100));
        } else {
            $discount = min($total, $promo->value);
        }

        session()->put('applied_promo', [
            'promo_id' => $promo->id,
            'code' => $promo->code,
            'type' => $promo->type,
            'value' => (float) $promo->value,
            'discount' => (float) $discount,
        ]);

        // Increment usage count
        $promo->usage_count = $promo->usage_count + 1;
        $promo->save();

        return response()->json([
            'success' => true,
            'message' => 'Promo applied successfully',
            'discount' => $discount,
            'final_total' => max(0, $total - $discount),
        ]);
    }
}
