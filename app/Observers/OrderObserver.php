<?php

namespace App\Observers;

use App\Models\Order;
use App\Mail\OrderStatusUpdated;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        if ($order->isDirty('status')) {
            $notes = $order->admin_notes ?? null;

            if ($order->user && $order->user->email) {
                Mail::to($order->user->email)->queue(new OrderStatusUpdated($order, $notes));
                Log::info('Queued order status email', ['order_id' => $order->id, 'user_id' => $order->user->id ?? null, 'email' => $order->user->email]);
            }
        }
    }
}
