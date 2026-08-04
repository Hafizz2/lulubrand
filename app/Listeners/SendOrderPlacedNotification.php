<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Jobs\SendTelegramMessage;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOrderPlacedNotification implements ShouldQueue
{
    public function handle(OrderPlaced $event): void
    {
        $order = $event->order;
        $ownerChatId = config('services.telegram.owner_chat_id');

        if (empty($ownerChatId)) {
            return;
        }

        $order->load(['bankAccount']);
        $totalFormatted = '$' . number_format($order->total / 100, 2);
        $depositFormatted = '$' . number_format($order->deposit_amount / 100, 2);
        $balanceFormatted = '$' . number_format($order->balance_due / 100, 2);

        $addressParts = array_filter([
            $order->customer_address,
            $order->customer_district,
            $order->customer_city,
            $order->customer_country ?: 'Ethiopia'
        ]);
        $fullAddress = implode(', ', $addressParts);

        $message = "🛍️ <b>NEW ORDER PLACED!</b>\n\n"
            . "📦 Order ID: <code>#{$order->order_number}</code>\n"
            . "👤 Customer: {$order->customer_name}\n"
            . "📞 Phone: <a href='tel:{$order->customer_phone}'>{$order->customer_phone}</a>\n"
            . "🚚 Logistics: " . strtoupper(str_replace('_', ' ', $order->logistics_mode)) . "\n"
            . "📍 Address: {$fullAddress}\n"
            . "📅 Schedule: " . ($order->preferred_date ? $order->preferred_date->format('M j, Y') : 'N/A') . " ({$order->preferred_time})\n\n"
            . "💰 Total: {$totalFormatted}\n";

        if ($order->deposit_amount > 0) {
            $message .= "💵 Deposit Due: {$depositFormatted} | Balance: {$balanceFormatted}\n";
        }

        $message .= "💳 Payment: " . strtoupper($order->payment_method) . "\n";

        if ($order->bankAccount) {
            $message .= "🏦 Bank: {$order->bankAccount->bank_name} ({$order->bankAccount->account_number})\n";
        }

        if (! empty($order->confirmed_transaction_id)) {
            if (filter_var($order->confirmed_transaction_id, FILTER_VALIDATE_URL)) {
                $message .= "🧾 Receipt Link: <a href='{$order->confirmed_transaction_id}'>Click to View Receipt</a>\n";
            } else {
                $message .= "🧾 Transaction ID: <code>{$order->confirmed_transaction_id}</code>\n";
            }
        }

        if (! empty($order->payment_proof)) {
            $message .= "📷 Payment Proof: <a href='" . config('app.url') . "{$order->payment_proof}'>View Uploaded Screenshot</a>\n";
        }

        $message .= "\n🔗 <a href=\"" . config('app.url') . "/admin/orders\">Manage Order in Admin</a>";

        SendTelegramMessage::dispatch($ownerChatId, $message);
    }
}
