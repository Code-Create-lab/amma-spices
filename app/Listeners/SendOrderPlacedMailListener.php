<?php

namespace App\Listeners;

use App\Events\SendOrderPlacedMailEvent;
use App\Mail\SendOrderPlaced;
use App\Models\Admin;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class SendOrderPlacedMailListener implements ShouldQueue
{
    // 
    public function handle(SendOrderPlacedMailEvent $event)
    {
        //  dd($event->order);

        $order = $event->order;
        // dd($order->address, $order);
        Mail::to([
            'sr.snehal369a@gmail.com',
            'shreyadiwivedi@gmail.com',
            $order->address->receiver_email
        ])->send(new SendOrderPlaced($order , 0));
      
        Mail::to([
           'sr.snehal369a@gmail.com',
           'shreyadiwivedi@gmail.com'
        ])->send(new SendOrderPlaced($order , 1));
    }
}
// 