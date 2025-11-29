<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Order;

class OrderStatusUpdated extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Order $order;
    public $previousStatus;
    public $notes;

    /**
     * Constructor accepts an Order and an optional second parameter used by callers
     * for either previous status or admin notes. We store it in both properties
     * so existing callers continue to work.
     */
    public function __construct(Order $order, $meta = null)
    {
        $this->order = $order;
        $this->previousStatus = $meta;
        $this->notes = $meta;
    }

    public function build()
    {
        $subject = 'Order #' . ($this->order->order_number ?? $this->order->id) . ' status: ' . $this->order->status;

        return $this->subject($subject)
                    ->view('emails.order_status_updated')
                    ->with([
                        'order' => $this->order,
                        'previousStatus' => $this->previousStatus,
                        'notes' => $this->notes,
                    ]);
    }
}
