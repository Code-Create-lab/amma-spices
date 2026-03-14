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
        $order = $event->order;
        $user = $event->order->user->email;
        Mail::to([
            Auth::user()->email,
            $order->address->receiver_email
        ])->send(new CompleteOrder($order));
    }
}
