<?php
// app/Mail/PendingRefundNotification.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PendingRefundNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $payment;

    public function __construct($order, $payment)
    {
        $this->order = $order;
        $this->payment = $payment;
    }

    public function build()
    {
        return $this->subject('Action Required: Pending Refund - Order #' . $this->order->cart_id)
                    ->view('emails.pending-refund');
    }
}