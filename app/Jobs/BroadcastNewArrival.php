<?php

namespace App\Jobs;

use App\Models\TelegramLink;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class BroadcastNewArrival implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(
        public readonly string $message,
        public readonly int $batchSize = 25,
        public readonly int $delayBetweenBatchesMs = 1000,
    ) {}

    public function handle(TelegramService $telegram): void
    {
        // Collect all unique subscriber chat_ids
        TelegramLink::whereNotNull('telegram_chat_id')
            ->select('telegram_chat_id')
            ->distinct()
            ->chunk($this->batchSize, function (Collection $batch) use ($telegram) {
                foreach ($batch as $link) {
                    $telegram->sendMessage($link->telegram_chat_id, $this->message);
                    // Respect Telegram rate limit: max 30 messages/second
                    usleep(50_000); // 50ms between each message
                }
                // Pause between batches to avoid API flood
                usleep($this->delayBetweenBatchesMs * 1000);
            });
    }
}
