<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Jobs\SendTelegramMessage;
use App\Models\TelegramLink;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendStatusChangedNotification implements ShouldQueue
{
    public function handle(OrderStatusChanged $event): void
    {
        $order = $event->order;

        // Find customer's linked Telegram chat_id (via user_id or order_id)
        $link = TelegramLink::where(function ($q) use ($order) {
            $q->where('order_id', $order->id);
            if ($order->user_id) {
                $q->orWhere('user_id', $order->user_id);
            }
        })->first();

        if (! $link) {
            return;
        }

        $statusEmoji = match ($event->newStatus) {
            'confirmed' => '✅',
            'packed'    => '📦',
            'shipped'   => '🚚',
            'delivered' => '🎉',
            'cancelled' => '❌',
            'refunded'  => '💸',
            default     => 'ℹ️',
        };

        $message = "{$statusEmoji} <b>Order Update</b>\n\n"
            . "Your order <code>{$order->order_number}</code> status has changed:\n"
            . "<b>" . strtoupper($event->oldStatus) . "</b> → <b>" . strtoupper($event->newStatus) . "</b>\n\n"
            . "Track your order: " . config('app.url') . "/order/{$order->order_number}";

        SendTelegramMessage::dispatch($link->telegram_chat_id, $message);
    }
}
