<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderStatusUpdated as OrderStatusUpdatedMail;
use Illuminate\Support\Collection;

class OrderService
{
    /**
     * Create a new order from cart items
     */
    public function createOrder(User $user, Collection $cartItems, ?int $shippingAddressId = null): Order
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

        // Site settings
        $deliveryFee = (float) \App\Models\SiteSetting::get('delivery_fee', 15);
        $deliveryThreshold = (float) \App\Models\SiteSetting::get('delivery_threshold', 200);
        $taxPercentage = (float) \App\Models\SiteSetting::get('tax_percentage', 14);
        $serviceFeePercentage = (float) \App\Models\SiteSetting::get('service_fee_percentage', 0);

        $finalAfterDiscount = max(0, $subtotal - $discountAmount);
        $shippingAmount = $finalAfterDiscount >= $deliveryThreshold ? 0 : $deliveryFee;
        $serviceFee = round($finalAfterDiscount * ($serviceFeePercentage / 100), 2);
        $taxAmount = round(($finalAfterDiscount + $serviceFee + $shippingAmount) * ($taxPercentage / 100), 2);
        $totalAmount = round(max(0, $finalAfterDiscount + $serviceFee + $shippingAmount + $taxAmount), 2);

        $order = Order::create([
            'user_id' => $user->id,
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
        foreach ($cartItems as $item) {
            // Allow cart item to carry fetched product data to avoid duplicate queries
            $product = null;
            if (!empty($item['product']) && is_array($item['product'])) {
                // we still want the Eloquent model when possible for relations
                $product = Product::find($item['product']['id']);
            } else {
                $product = Product::find($item['product_id']);
            }

            if (!$product) {
                continue;
            }

            $mainImage = $product->main_image;

            // Store full product snapshot for historical reference
            $productSnapshot = [
                'id' => $product->id,
                'sku' => $product->sku,
                'title' => $product->title,
                'description' => $product->description,
                'price' => (float) $product->price,
                'currency' => $product->currency,
                'collection_title' => $product->collection->title ?? null,
                'is_one_of_a_kind' => (bool) $product->is_one_of_a_kind,
                'main_image_url' => $mainImage?->url,
            ];

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'price' => $product->price,
                'quantity' => $item['quantity'],
                'size_label' => $item['size_label'] ?? null,
                'subtotal' => $product->price * $item['quantity'],
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
        $total = 0;

        foreach ($cartItems as $item) {
            $product = Product::find($item['product_id']);
            if ($product) {
                $total += $product->price * $item['quantity'];
            }
        }

        return round($total, 2);
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
            \Log::error('Failed to queue order status email for order ' . $order->id . ': ' . $e->getMessage());
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
    public function cancelOrder(Order $order, string $reason = null): Order
    {
        // Can only cancel orders that haven't been shipped
        if (in_array($order->status, ['shipped', 'completed'])) {
            throw new \Exception('Cannot cancel order that has already been shipped or completed.');
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
        }

        // Mark that stock has been decremented for this order
        $order->update(['stock_decremented' => true]);
    }

    /**
     * Validate cart items before creating order
     */
    public function validateCartItems(Collection $cartItems): array
    {
        $errors = [];

        foreach ($cartItems as $index => $item) {
            $product = Product::find($item['product_id']);

            if (!$product) {
                $errors[] = "Product not found for item {$index}";
                continue;
            }

            if (!$product->getAttribute('visible')) {
                $errors[] = "Product '{$product->title}' is no longer available";
                continue;
            }

            if ($product->is_one_of_a_kind) {
                if ($product->quantity < $item['quantity']) {
                    $errors[] = "Product '{$product->title}' does not have sufficient stock";
                }
            } else {
                $productSize = $product->sizes()
                    ->where('size_label', $item['size_label'])
                    ->first();

                if (!$productSize) {
                    $errors[] = "Size '{$item['size_label']}' not available for '{$product->title}'";
                } elseif ($productSize->quantity < $item['quantity']) {
                    $errors[] = "Size '{$item['size_label']}' for '{$product->title}' does not have sufficient stock";
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
