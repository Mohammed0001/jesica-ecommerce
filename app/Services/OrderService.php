<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderStatusUpdated as OrderStatusUpdatedMail;
use Illuminate\Support\Collection;

class OrderService
{
    /**
     * Create a new order from cart items
     */
    public function createOrder(?User $user, Collection $cartItems, ?int $shippingAddressId = null, ?string $city = null, ?string $district = null, ?string $country = 'Egypt', array $guestData = []): Order
    {
        // Calculate subtotal from product prices
        $subtotal = $this->calculateTotal($cartItems);

        // Promo/discount from session if present
        $appliedPromo = session()->get('applied_promo', null);
        $discountAmount = 0;
        if ($appliedPromo) {
            if ($appliedPromo['type'] === 'percentage') {
                $discountAmount = ($subtotal * ($appliedPromo['value'] / 100));
            } else {
                $discountAmount = $appliedPromo['value'];
            }
        }

        // Per-area delivery fee (falls back to global site setting for unknown cities)
        $deliveryFee = \App\Models\Region::getDeliveryFeeForLocation($city, $district, $country);
        $deliveryThreshold = (float) \App\Models\SiteSetting::get('delivery_threshold', 200);
        $taxPercentage = (float) \App\Models\SiteSetting::get('tax_percentage', 14);
        $serviceFeePercentage = (float) \App\Models\SiteSetting::get('service_fee_percentage', 0);

        $finalAfterDiscount = max(0, $subtotal - $discountAmount);
        $shippingAmount = $finalAfterDiscount >= $deliveryThreshold ? 0 : $deliveryFee;
        $serviceFee = round($finalAfterDiscount * ($serviceFeePercentage / 100), 2);
        $taxAmount = round(($finalAfterDiscount + $serviceFee + $shippingAmount) * ($taxPercentage / 100), 2);
        $totalAmount = round(max(0, $finalAfterDiscount + $serviceFee + $shippingAmount + $taxAmount), 2);

        $order = Order::create([
            'user_id' => $user?->id,
            'guest_email' => $guestData['email'] ?? null,
            'guest_name' => $guestData['name'] ?? null,
            'guest_phone' => $guestData['phone'] ?? null,
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'shipping_amount' => $shippingAmount,
            'service_fee' => $serviceFee,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'status' => 'draft',
            'shipping_address_id' => $shippingAddressId,
        ]);

        // Create order items
        $products = $this->resolveProducts($cartItems);

        foreach ($cartItems as $item) {
            $product = $products->get($item['product_id']);

            if (!$product) {
                continue;
            }

            $mainImage = $product->main_image;
            // The customer pays the sale price when a sale is running; `price`
            // is kept in the snapshot so the discount stays auditable.
            $unitPrice = $product->effective_price;

            // Store full product snapshot for historical reference
            $productSnapshot = [
                'id' => $product->id,
                'sku' => $product->sku,
                'title' => $product->title,
                'description' => $product->description,
                'price' => (float) $product->price,
                'sale_price' => $product->isOnSale() ? (float) $product->sale_price : null,
                'was_on_sale' => $product->isOnSale(),
                'currency' => $product->currency,
                'collection_title' => $product->collection->title ?? null,
                'is_one_of_a_kind' => (bool) $product->is_one_of_a_kind,
                'color_name' => $item['color_name'] ?? null,
                'main_image_url' => $mainImage?->url,
            ];

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'price' => $unitPrice,
                'quantity' => $item['quantity'],
                'size_label' => $item['size_label'] ?? null,
                'color_name' => $item['color_name'] ?? null,
                'subtotal' => round($unitPrice * $item['quantity'], 2),
                'product_snapshot' => $productSnapshot,
            ]);
        }

        return $order->load('items.product', 'shippingAddress');
    }

    /**
     * Calculate total amount for cart items
     */
    public function calculateTotal(Collection $cartItems): float
    {
        $products = $this->resolveProducts($cartItems);
        $total = 0;

        foreach ($cartItems as $item) {
            $product = $products->get($item['product_id']);
            if ($product) {
                $total += $product->effective_price * $item['quantity'];
            }
        }

        return round($total, 2);
    }

    /**
     * Load every product referenced by the cart in one query, keyed by id.
     *
     * Cart lines arrive either as plain ids or with a pre-fetched product
     * array; both are handled here so callers never issue a query per line.
     */
    private function resolveProducts(Collection $cartItems): \Illuminate\Support\Collection
    {
        $ids = $cartItems
            ->map(fn ($item) => $item['product_id'] ?? ($item['product']['id'] ?? null))
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return Product::with(['images', 'collection'])
            ->whereIn('id', $ids->all())
            ->get()
            ->keyBy('id');
    }

    /**
     * Update order status
     */
    public function updateOrderStatus(Order $order, string $status): Order
    {
        $previous = $order->status;
        $order->update(['status' => $status]);

        // Queue an email to the customer notifying about status change
        try {
            if ($order->user && $order->user->email) {
                Mail::to($order->user->email)->queue(new OrderStatusUpdatedMail($order, $previous));
            }
        } catch (\Exception $e) {
            // swallow to avoid breaking status updates; log if necessary
            Log::error('Failed to queue order status email for order ' . $order->id . ': ' . $e->getMessage());
        }

        // Handle status-specific actions
        switch ($status) {
            case 'shipped':
                $order->update(['shipped_at' => now()]);
                // TODO: Send shipping notification email
                break;
            case 'completed':
                $order->update(['completed_at' => now()]);
                // TODO: Send completion notification email
                break;
        }

        return $order;
    }

    /**
     * Cancel an order
     */
    public function cancelOrder(Order $order, ?string $reason = null): Order
    {
        // Can only cancel orders that haven't been shipped
        if (in_array($order->status, ['shipped', 'completed'])) {
            throw new \Exception(sprintf(
                'Order %s cannot be cancelled because it has already been marked as %s. Please request a return instead.',
                $order->order_number,
                $order->status
            ));
        }

        $order->update([
            'status' => 'cancelled',
            'notes' => $reason ? "Cancelled: {$reason}" : 'Order cancelled',
        ]);

        // Restore product quantities only if they were decremented previously
        if ($order->stock_decremented) {
            foreach ($order->items as $item) {
            $product = $item->product;
            if ($product) {
                if ($product->is_one_of_a_kind) {
                    $product->increment('quantity', $item->quantity);
                } else {
                    // For multi-size products, restore size quantity
                    $productSize = $product->sizes()
                        ->where('size_label', $item->size_label)
                        ->first();

                    if ($productSize) {
                        $productSize->increment('quantity', $item->quantity);
                    }
                }

                $this->syncSoldOutStatus($product);
            }
            }

            // mark that stock was restored
            $order->update(['stock_decremented' => false]);
        }

        // TODO: Process refunds if needed
        // TODO: Send cancellation notification email

        return $order;
    }

    /**
     * Reduce product quantities when order is placed
     */
    public function decrementStock(Order $order): void
    {
        // Prevent double-decrementing stock for the same order
        if ($order->stock_decremented) {
            return;
        }

        foreach ($order->items as $item) {
            $product = $item->product;
            if (!$product) {
                continue;
            }

            if ($product->is_one_of_a_kind) {
                // For one-of-a-kind products, decrement main quantity
                $product->decrement('quantity', $item->quantity);
            } else {
                // For multi-size products, decrement size quantity
                $productSize = $product->sizes()
                    ->where('size_label', $item->size_label)
                    ->first();

                if ($productSize) {
                    $productSize->decrement('quantity', $item->quantity);
                }
            }

            $this->syncSoldOutStatus($product);
        }

        // Mark that stock has been decremented for this order
        $order->update(['stock_decremented' => true]);
    }

    /**
     * Clear the manual sold-out flag once a product is back in stock.
     *
     * This deliberately only ever clears the flag, never sets it. `is_sold_out`
     * is the admin's manual override ("Mark as Sold Out" in the product form);
     * running out of stock is already reflected by Product::isAvailable(), so
     * writing the flag on every order both duplicated that logic and silently
     * overwrote the admin's choice. Worse, it used to read stock as "has a
     * ProductSize row with quantity > 0", so every product without size rows
     * got permanently flagged sold out in the database the first time it was
     * ordered -- which then had to be undone by hand.
     */
    private function syncSoldOutStatus(Product $product): void
    {
        $product->refresh();
        $product->load('sizes');

        if ($product->is_sold_out && $product->hasStock()) {
            $product->update(['is_sold_out' => false]);
        }
    }

    /**
     * Validate cart items before creating order
     */
    public function validateCartItems(Collection $cartItems): array
    {
        $errors = [];

        $products = Product::with(['sizes', 'colors'])
            ->whereIn('id', $cartItems->pluck('product_id')->filter()->unique()->all())
            ->get()
            ->keyBy('id');

        foreach ($cartItems as $index => $item) {
            $product = $products->get($item['product_id']);
            $position = $index + 1;

            if (!$product) {
                $errors[] = "Item {$position} in your bag no longer exists in the store. Please remove it and try again.";
                continue;
            }

            if (!$product->getAttribute('visible')) {
                $errors[] = "'{$product->title}' has been removed from the store. Please take it out of your bag to continue.";
                continue;
            }

            if ($product->is_one_of_a_kind) {
                if ($product->quantity < $item['quantity']) {
                    $errors[] = $product->quantity > 0
                        ? "Only {$product->quantity} left of '{$product->title}', but your bag has {$item['quantity']}."
                        : "'{$product->title}' has just sold out.";
                }
            } else {
                // Only validate size for multi-size products
                if (empty($item['size_label'])) {
                    // Check if product actually has sizes
                    if ($product->sizes->isNotEmpty()) {
                        $offered = $product->sizes->where('quantity', '>', 0)->pluck('size_label')->implode(', ');
                        $errors[] = "Please select a size for '{$product->title}'"
                            . ($offered !== '' ? " (available: {$offered})" : '') . '.';
                    }
                } else {
                    $productSize = $product->sizes->firstWhere('size_label', $item['size_label']);

                    if (!$productSize) {
                        $offered = $product->sizes->where('quantity', '>', 0)->pluck('size_label')->implode(', ');
                        $errors[] = "Size '{$item['size_label']}' is no longer offered for '{$product->title}'"
                            . ($offered !== '' ? ". Available sizes: {$offered}" : '') . '.';
                    } elseif ($productSize->quantity < $item['quantity']) {
                        $errors[] = $productSize->quantity > 0
                            ? "Only {$productSize->quantity} left of '{$product->title}' in size {$item['size_label']}, but your bag has {$item['quantity']}."
                            : "'{$product->title}' in size {$item['size_label']} has just sold out.";
                    }
                }
            }

            // Colour is a presentation variant: it must still be on offer.
            $availableColors = $product->available_colors;

            if ($availableColors->isNotEmpty()) {
                $chosen = $item['color_name'] ?? null;

                if (empty($chosen)) {
                    $errors[] = "Please select a colour for '{$product->title}' (available: "
                        . $availableColors->pluck('name')->implode(', ') . ').';
                } elseif (!$availableColors->contains(fn ($color) => $color->name === $chosen)) {
                    $errors[] = "Colour '{$chosen}' is no longer available for '{$product->title}'. Available colours: "
                        . $availableColors->pluck('name')->implode(', ') . '.';
                }
            }
        }

        return $errors;
    }

    /**
     * Export orders to CSV
     */
    public function exportOrdersToCsv($orders): string
    {
        $headers = [
            'Order Number',
            'Customer Name',
            'Customer Email',
            'Status',
            'Total Amount',
            'Total Paid',
            'Remaining Balance',
            'Order Date',
            'Shipped Date',
            'Completed Date'
        ];

        $filename = 'orders_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $filePath = storage_path('app/exports/' . $filename);

        // Ensure directory exists
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        $file = fopen($filePath, 'w');
        fputcsv($file, $headers);

        foreach ($orders as $order) {
            $row = [
                $order->order_number,
                $order->user->name,
                $order->user->email,
                $order->status,
                $order->total_amount,
                $order->total_paid,
                $order->remaining_balance,
                $order->created_at->format('Y-m-d H:i:s'),
                $order->shipped_at?->format('Y-m-d H:i:s') ?? '',
                $order->completed_at?->format('Y-m-d H:i:s') ?? '',
            ];
            fputcsv($file, $row);
        }

        fclose($file);

        return $filePath;
    }
}
