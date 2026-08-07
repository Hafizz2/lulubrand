<?php

namespace App\Jobs;

use App\Services\WebPushService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWebPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public ?int $userId,
        public string $title,
        public string $body,
        public ?string $url = null,
        public ?string $icon = null,
        public string $target = 'user' // 'user' or 'broadcast_all' or 'broadcast_customers'
    ) {}

    public function handle(WebPushService $pushService): void
    {
        if ($this->target === 'broadcast_all') {
            $pushService->broadcast('all', $this->title, $this->body, $this->url, $this->icon);
        } elseif ($this->target === 'broadcast_customers') {
            $pushService->broadcast('customers', $this->title, $this->body, $this->url, $this->icon);
        } elseif ($this->userId) {
            $pushService->sendToUser($this->userId, $this->title, $this->body, $this->url, $this->icon);
        }
    }
}
