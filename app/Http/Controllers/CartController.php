<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
            'color_name' => 'nullable|string',
        ], [
            'product_id.required' => 'No product was submitted. Please reload the page and try again.',
            'product_id.exists' => 'That product is no longer in the store.',
            'quantity.min' => 'Please choose a quantity of at least 1.',
            'quantity.integer' => 'Quantity must be a whole number.',
        ]);

        $product = Product::with(['sizes', 'colors'])->findOrFail($request->product_id);

        // Check if product is available
        if (!$product->getAttribute('visible')) {
            return $this->addFailed($request, sprintf('"%s" is no longer listed in the store.', $product->title));
        }

        if (!$product->isAvailable()) {
            return $this->addFailed($request, sprintf('"%s" is sold out.', $product->title));
        }

        // Validate size for multi-size products
        if (!$product->is_one_of_a_kind && $product->sizes && $product->sizes->count() > 0) {
            if (!$request->size_label) {
                $offered = $product->sizes->where('quantity', '>', 0)->pluck('size_label')->implode(', ');

                return $this->addFailed($request, sprintf(
                    'Please choose a size for "%s"%s.',
                    $product->title,
                    $offered !== '' ? ' (available: ' . $offered . ')' : ''
                ));
            }

            $productSize = $product->sizes->firstWhere('size_label', $request->size_label);

            if (!$productSize) {
                $offered = $product->sizes->where('quantity', '>', 0)->pluck('size_label')->implode(', ');

                return $this->addFailed($request, sprintf(
                    'Size "%s" is not offered for "%s"%s.',
                    $request->size_label,
                    $product->title,
                    $offered !== '' ? '. Available sizes: ' . $offered : ''
                ));
            }

            if ($productSize->quantity < $request->quantity) {
                return $this->addFailed($request, sprintf(
                    'Only %d left of "%s" in size %s, but you asked for %d.',
                    $productSize->quantity,
                    $product->title,
                    $request->size_label,
                    $request->quantity
                ));
            }
        }

        // Validate colour for products that offer a choice
        $availableColors = $product->available_colors;
        $colorName = $request->color_name;

        if ($availableColors->isNotEmpty()) {
            if (!$colorName) {
                return $this->addFailed($request, sprintf(
                    'Please choose a colour for "%s" (available: %s).',
                    $product->title,
                    $availableColors->pluck('name')->implode(', ')
                ));
            }

            if (!$availableColors->contains(fn ($color) => $color->name === $colorName)) {
                return $this->addFailed($request, sprintf(
                    'Colour "%s" is not available for "%s". Available colours: %s.',
                    $colorName,
                    $product->title,
                    $availableColors->pluck('name')->implode(', ')
                ));
            }
        } else {
            // Product has no colour options, so ignore anything submitted.
            $colorName = null;
        }

        // Add to cart
        $cart = session()->get('cart', []);
        $cartKey = $this->cartKey($product->id, $request->size_label, $colorName);

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += $request->quantity;
        } else {
            $cart[$cartKey] = [
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'size_label' => $request->size_label,
                'color_name' => $colorName,
                'price' => $product->effective_price,
                'title' => $product->title,
                'image' => $product->main_image?->url,
            ];
        }

        session()->put('cart', $cart);

        // If it's an AJAX request, return JSON with success and updated cart count
        if ($request->wantsJson() || $request->ajax()) {
            $cartCount = array_sum(array_column($cart, 'quantity'));
            return response()->json(['success' => true, 'message' => 'Product added to cart!', 'cartCount' => $cartCount]);
        }

        return back()->with('success', 'Product added to cart!');
    }

    /**
     * Build the session key that identifies one cart line. Size and colour are
     * part of it so the same product in two variants stays two lines.
     */
    private function cartKey(int $productId, ?string $sizeLabel, ?string $colorName): string
    {
        return implode('_', [
            $productId,
            $sizeLabel ?: 'one_size',
            $colorName ? Str::slug($colorName) : 'default',
        ]);
    }

    /**
     * Return an add-to-cart failure the same way for AJAX and form posts
     */
    private function addFailed(Request $request, string $message)
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => false, 'message' => $message], 422);
        }

        return back()->with('error', $message);
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
        ], [
            'quantity.min' => 'Quantity must be at least 1. Use Remove to take the item out of your bag.',
            'quantity.integer' => 'Quantity must be a whole number.',
        ]);

        $cart = session()->get('cart', []);

        if (!isset($cart[$request->cart_key])) {
            $message = 'That item is no longer in your bag. Refresh the page to see the current contents.';

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 404);
            }

            return back()->with('error', $message);
        }

        $cart[$request->cart_key]['quantity'] = $request->quantity;
        session()->put('cart', $cart);

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
     * Get cart items with product details.
     *
     * Products are fetched in a single query rather than one per line, which
     * is what made a busy cart page slow.
     */
    private function getCartItems()
    {
        $cart = session()->get('cart', []);
        $cartItems = collect();

        if (empty($cart)) {
            return $cartItems;
        }

        $products = Product::with(['images', 'colors'])
            ->whereIn('id', collect($cart)->pluck('product_id')->unique()->all())
            ->get()
            ->keyBy('id');

        $currency = session('currency', 'EGP');

        foreach ($cart as $key => $item) {
            $product = $products->get($item['product_id']);

            if (!$product) {
                continue;
            }

            $displayPrice = $product->convertToCurrency($currency);

            $cartItems->push([
                'cart_key' => $key,
                'product_id' => $product->id,
                'product' => $product,
                'quantity' => $item['quantity'],
                'size_label' => $item['size_label'] ?? null,
                'color_name' => $item['color_name'] ?? null,
                'price' => $product->effective_price, // original numeric price stored on product
                'is_on_sale' => $product->isOnSale(),
                'display_price' => $displayPrice,
                'formatted_price' => $product->formatted_price,
                'formatted_original_price' => $product->formatted_original_price,
                'display_subtotal' => round($displayPrice * $item['quantity'], 2),
                'subtotal' => round($product->effective_price * $item['quantity'], 2), // original currency subtotal
            ]);
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
        ], [
            'promo_code.required' => 'Enter a promo code first.',
        ]);

        // Case-insensitive search
        $promoCode = strtoupper(trim($request->promo_code));
        $promo = PromoCode::whereRaw('UPPER(code) = ?', [$promoCode])->first();

        if (!$promo) {
            return response()->json(['success' => false, 'message' => 'No promo code matches "' . $promoCode . '". Check the spelling and try again.'], 422);
        }

        if (!$promo->active) {
            return response()->json(['success' => false, 'message' => 'Promo code "' . $promo->code . '" is not active.'], 422);
        }

        if ($promo->isExpired()) {
            $expiry = $promo->expires_at ? $promo->expires_at->format('j M Y') : null;

            return response()->json([
                'success' => false,
                'message' => 'Promo code "' . $promo->code . '" expired' . ($expiry ? ' on ' . $expiry : '') . '.',
            ], 422);
        }

        if ($promo->max_uses !== null && $promo->usage_count >= $promo->max_uses) {
            return response()->json([
                'success' => false,
                'message' => 'Promo code "' . $promo->code . '" has reached its limit of ' . $promo->max_uses . ' uses.',
            ], 422);
        }

        $cartItems = $this->getCartItems();

        if ($cartItems->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Your bag is empty, so there is nothing to discount yet.'], 422);
        }

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

    /**
     * Remove applied promo code from cart
     */
    public function removePromo()
    {
        session()->forget('applied_promo');

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Promo code removed']);
        }

        return back()->with('success', 'Promo code removed');
    }
}
