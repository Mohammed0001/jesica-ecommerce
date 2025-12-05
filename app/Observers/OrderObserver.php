<?php

namespace App\Observers;

use App\Models\Order;
use App\Mail\OrderStatusUpdated;
use App\Services\BostaService;
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

            // Send status update email
            if ($order->user && $order->user->email) {
                Mail::to($order->user->email)->queue(new OrderStatusUpdated($order, $notes));
                Log::info('Queued order status email', ['order_id' => $order->id, 'user_id' => $order->user->id ?? null, 'email' => $order->user->email]);
            }

            // Auto-create BOSTA shipment when status changes to 'processing'
            if ($order->status === 'processing' && !$order->shipment) {
                try {
                    $bostaService = app(BostaService::class);
                    $shipment = $bostaService->createShipment($order);

                    if ($shipment) {
                        Log::info('BOSTA shipment created automatically', [
                            'order_id' => $order->id,
                            'shipment_id' => $shipment->id,
                            'tracking_number' => $shipment->tracking_number,
                        ]);
                    } else {
                        Log::warning('Failed to create BOSTA shipment automatically', [
                            'order_id' => $order->id,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Error creating BOSTA shipment', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }
}
