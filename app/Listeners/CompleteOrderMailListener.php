<?php

namespace App\Listeners;

use App\Events\CompleteOrderMailEvent;
use App\Mail\CompleteOrder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class CompleteOrderMailListener
{
    public function handle(CompleteOrderMailEvent $event)
    {
        $order = $event->order->loadMissing(['user', 'address', 'orderItems.variation']);

        $recipients = array_values(array_unique(array_filter([
            Auth::guard('admin')->user()?->email,
            $order->address?->receiver_email,
            $order->user?->email,
        ])));

        if (empty($recipients)) {
            return;
        }

        Mail::to($recipients)->send(new CompleteOrder($order));
    }
}
