<?php

namespace App\Jobs;

use App\Services\OrderNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendOrderNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public int $orderId;
    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
    }

    public function handle(OrderNotificationService $service)
    {
        $service->sendOrderNotifications($this->orderId);
    }
}
