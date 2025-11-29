<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $order;
    public $previousStatus;

    public function __construct($order, $previousStatus = null)
    {
        $this->order = $order;
        $this->previousStatus = $previousStatus;
        $this->subject('Your order status has been updated');
    }

    public function build()
    {
        return $this->view('emails.order_status_updated')
            ->with(['order' => $this->order, 'previousStatus' => $this->previousStatus])
            ->subject('Order #' . ($this->order->order_number ?? $this->order->id) . ' status: ' . $this->order->status);
    }
}
