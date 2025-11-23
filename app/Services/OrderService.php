<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;

class OrderService
{
    /**
     * Create a new order from cart items
     */
    public function createOrder(User $user, Collection $cartItems, ?int $shippingAddressId = null): Order
    {
        $totalAmount = $this->calculateTotal($cartItems);

        $order = Order::create([
            'user_id' => $user->id,
            'total_amount' => $totalAmount,
            'status' => 'draft',
            'shipping_address_id' => $shippingAddressId,
        ]);

        // Create order items
        foreach ($cartItems as $item) {
            $product = Product::find($item['product_id']);

            if (!$product) {
                continue;
            }

            // Store product snapshot for historical reference
            $productSnapshot = [
                'title' => $product->title,
                'description' => $product->description,
                'sku' => $product->sku,
                'collection_title' => $product->collection->title ?? null,
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
        $order->update(['status' => $status]);

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

        // Restore product quantities
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

        // TODO: Process refunds if needed
        // TODO: Send cancellation notification email

        return $order;
    }

    /**
     * Reduce product quantities when order is placed
     */
    public function decrementStock(Order $order): void
    {
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
