<?php

namespace App\Listeners;

use App\Jobs\SendTelegramMessage;
use App\Models\ProductVariant;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SendLowStockAlert implements ShouldQueue
{
    private const THRESHOLD = 5;
    private const DEBOUNCE_MINUTES = 60;

    public function handle(object $event): void
    {
        // This listener is called whenever stock is adjusted — check if we need to alert
        $variant = $event->variant ?? null;
        if (! $variant instanceof ProductVariant) {
            return;
        }

        if ($variant->stock_quantity > self::THRESHOLD) {
            return;
        }

        $ownerChatId = config('services.telegram.owner_chat_id');
        if (empty($ownerChatId)) {
            return;
        }

        // Debounce: only alert once per variant per hour
        $cacheKey = "low_stock_alert_sent_{$variant->id}";
        if (Cache::has($cacheKey)) {
            return;
        }

        Cache::put($cacheKey, true, now()->addMinutes(self::DEBOUNCE_MINUTES));

        $variant->loadMissing('product');
        $message = "⚠️ <b>Low Stock Alert</b>\n\n"
            . "Product: <b>{$variant->product->title}</b>\n"
            . "SKU: <code>{$variant->sku}</code>\n"
            . "Remaining: <b>{$variant->stock_quantity} units</b>\n\n"
            . "🔗 <a href=\"" . config('app.url') . "/admin/stock\">Manage Stock</a>";

        SendTelegramMessage::dispatch($ownerChatId, $message);
    }
}
