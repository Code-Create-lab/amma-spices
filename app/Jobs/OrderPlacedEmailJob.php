<?php

namespace App\Jobs;

use App\Mail\SendOrderPlaced;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;


class OrderPlacedEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public $order;
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        // dd($this->order);
        Mail::to([$this->order->address->receiver_email])
            ->bcc([
                'sr.snehal369a@gmail.com', 'shreyadiwivedi@gmail.com',
            ])->send(new SendOrderPlaced($this->order, 0));

        Mail::to(['sr.snehal369a@gmail.com'])->bcc([
            'sr.snehal369a@gmail.com',
            'shreyadiwivedi@gmail.com',
        ])->send(new SendOrderPlaced($this->order, 1));
    }
}
