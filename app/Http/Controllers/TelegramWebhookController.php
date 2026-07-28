<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\TelegramLink;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Verify secret token header (Telegram sends X-Telegram-Bot-Api-Secret-Token)
        $secret = config('services.telegram.webhook_secret');
        if ($secret && $request->header('X-Telegram-Bot-Api-Secret-Token') !== $secret) {
            Log::warning('Telegram webhook: invalid secret token');
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $payload = $request->json()->all();
        Log::debug('Telegram webhook received', ['payload' => $payload]);

        // 1. Handle Callback Query (Inline Button Click)
        if (isset($payload['callback_query'])) {
            $this->handleCallbackQuery($payload['callback_query']);
            return response()->json(['ok' => true]);
        }

        // 2. Handle Text Message
        $message = $payload['message'] ?? $payload['edited_message'] ?? null;
        if (! $message) {
            return response()->json(['ok' => true]);
        }

        $chatId = $message['chat']['id'] ?? null;
        $text = trim($message['text'] ?? '');

        if (! $chatId || ! $text) {
            return response()->json(['ok' => true]);
        }

        $this->routeCommand($chatId, $text);

        return response()->json(['ok' => true]);
    }

    private function handleCallbackQuery(array $callbackQuery): void
    {
        $chatId = $callbackQuery['message']['chat']['id'] ?? null;
        $data = $callbackQuery['data'] ?? '';

        if (! $chatId) return;

        match ($data) {
            'btn_my_orders' => $this->handleOrders($chatId),
            'btn_new_arrivals' => $this->handleNewArrivals($chatId),
            'btn_contact_support' => $this->sendReply($chatId,
                "📞 <b>LULU Couture Support</b>\n\n"
                . "📱 Phone: +251 911 234 567\n"
                . "📍 Store: Addis Ababa, Ethiopia\n"
                . "⏰ Hours: Mon - Sat (9:00 AM - 8:00 PM)",
                $this->getMainMenuKeyboard()
            ),
            default => $this->handleTrack($chatId, str_replace('track_', '', $data)),
        };
    }

    private function routeCommand(int|string $chatId, string $text): void
    {
        $parts = explode(' ', $text, 2);
        $command = strtolower($parts[0]);
        $args = trim($parts[1] ?? '');

        match ($command) {
            '/start' => $this->handleStart($chatId, $args),
            '/track' => $this->handleTrack($chatId, $args),
            '/orders' => $this->handleOrders($chatId),
            '/newarrivals' => $this->handleNewArrivals($chatId),
            '/unlink' => $this->handleUnlink($chatId),
            default  => $this->sendWelcomeMessage($chatId),
        };
    }

    private function sendWelcomeMessage(int|string $chatId): void
    {
        $text = "👑 <b>Welcome to LULU Couture Bot</b>\n\n"
              . "Your luxury shopping assistant for real-time order tracking, new collection alerts, and customer support.\n\n"
              . "Tap any button below to get started:";

        $this->sendReply($chatId, $text, $this->getMainMenuKeyboard());
    }

    private function getMainMenuKeyboard(): array
    {
        return [
            'inline_keyboard' => [
                [
                    ['text' => '📦 My Orders', 'callback_data' => 'btn_my_orders'],
                    ['text' => '✨ New Arrivals', 'callback_data' => 'btn_new_arrivals'],
                ],
                [
                    ['text' => '📞 Contact Support', 'callback_data' => 'btn_contact_support'],
                    ['text' => '🛍️ Visit Storefront', 'url' => config('app.url', 'http://localhost:8000')],
                ]
            ]
        ];
    }

    private function handleStart(int|string $chatId, string $args): void
    {
        if (empty($args)) {
            $this->sendWelcomeMessage($chatId);
            return;
        }

        // Try to find by order number
        if (str_starts_with(strtoupper($args), 'LULU-')) {
            $orderNum = strtoupper(trim($args));
            $order = Order::with('items')->where('order_number', $orderNum)->first();

            if ($order) {
                TelegramLink::updateOrCreate(
                    ['telegram_chat_id' => (string) $chatId],
                    [
                        'order_id' => $order->id,
                        'user_id' => $order->user_id,
                        'phone_number' => $order->customer_phone,
                    ]
                );

                $itemsText = "";
                if ($order->items && count($order->items) > 0) {
                    foreach ($order->items as $item) {
                        $itemsText .= "• {$item->product_title} x{$item->quantity}\n";
                    }
                } else {
                    $itemsText = "• LULU Couture Items\n";
                }

                $msg = "✅ <b>Order Linked & Tracked!</b>\n\n"
                     . "Order Ref: <code>{$order->order_number}</code>\n"
                     . "Customer: <b>{$order->customer_name}</b>\n"
                     . "Status: <b>" . strtoupper($order->status) . "</b>\n"
                     . "Payment: <b>" . strtoupper($order->payment_status) . "</b>\n"
                     . "Total: <b>$" . number_format($order->total / 100, 2) . "</b>\n\n"
                     . "🛍️ <b>Items Ordered:</b>\n{$itemsText}\n"
                     . "📍 Delivery: {$order->customer_address}, {$order->customer_city}\n\n"
                     . "You will receive automatic notifications as your order is packed, shipped, and delivered!";

                $this->sendReply($chatId, $msg, $this->getMainMenuKeyboard());
                return;
            } else {
                TelegramLink::updateOrCreate(
                    ['telegram_chat_id' => (string) $chatId],
                    ['order_id' => null]
                );

                $msg = "✨ <b>Welcome to LULU Couture Bot!</b>\n\n"
                     . "Tracked Code: <code>{$orderNum}</code>\n"
                     . "Status: ⏳ <b>ORDER RECEIVED / PROCESSING</b>\n\n"
                     . "Your order is being processed by our store team. You will receive real-time notifications right here on Telegram as soon as your order status changes!";

                $this->sendReply($chatId, $msg, $this->getMainMenuKeyboard());
                return;
            }
        }

        // Phone lookup fallback
        $phone = preg_replace('/\s+/', '', $args);
        $order = Order::where('customer_phone', $phone)->latest()->first();

        if ($order) {
            TelegramLink::updateOrCreate(
                ['telegram_chat_id' => (string) $chatId],
                [
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'phone_number' => $phone,
                ]
            );
            $this->sendReply($chatId,
                "✅ <b>Phone Linked!</b>\n\n"
                . "Linked to <code>{$phone}</code>. Latest Order: <code>{$order->order_number}</code> (" . strtoupper($order->status) . ").",
                $this->getMainMenuKeyboard()
            );
        } else {
            $this->sendReply($chatId,
                "❌ No order found for <code>{$args}</code>.",
                $this->getMainMenuKeyboard()
            );
        }
    }

    private function handleTrack(int|string $chatId, string $args): void
    {
        if (empty($args)) {
            $this->sendReply($chatId, "📦 Usage: <code>/track LULU-ABC123</code>", $this->getMainMenuKeyboard());
            return;
        }

        $order = Order::with('items')->where('order_number', strtoupper(trim($args)))->first();

        if (! $order) {
            $this->sendReply($chatId, "❌ Order <code>" . strtoupper($args) . "</code> not found.", $this->getMainMenuKeyboard());
            return;
        }

        $statusEmoji = match ($order->status) {
            'pending'   => '⏳',
            'confirmed' => '✅',
            'packed'    => '📦',
            'shipped'   => '🚚',
            'delivered' => '🎉',
            'cancelled' => '❌',
            'refunded'  => '💸',
            default     => 'ℹ️',
        };

        $itemsText = "";
        if ($order->items) {
            foreach ($order->items as $item) {
                $itemsText .= "• {$item->product_title} x{$item->quantity} — $" . number_format($item->total_price / 100, 2) . "\n";
            }
        }

        $this->sendReply($chatId,
            "📋 <b>Order #{ $order->order_number } Details</b>\n\n"
            . "Status: {$statusEmoji} <b>" . strtoupper($order->status) . "</b>\n"
            . "Customer: {$order->customer_name} ({$order->customer_phone})\n"
            . "Payment: <b>" . strtoupper($order->payment_status) . "</b> ({$order->payment_method})\n"
            . "Schedule: {$order->preferred_date} ({$order->preferred_time})\n\n"
            . "🛍️ <b>Items:</b>\n{$itemsText}\n"
            . "Total: <b>$" . number_format($order->total / 100, 2) . "</b>",
            $this->getMainMenuKeyboard()
        );
    }

    private function handleOrders(int|string $chatId): void
    {
        $link = TelegramLink::where('telegram_chat_id', (string) $chatId)->first();

        $orders = Order::where(function ($q) use ($link, $chatId) {
            if ($link && $link->user_id) $q->where('user_id', $link->user_id);
            if ($link && $link->phone_number) $q->orWhere('customer_phone', $link->phone_number);
        })->latest()->take(5)->get();

        if ($orders->isEmpty()) {
            $this->sendReply($chatId, "📦 You have no orders linked yet.", $this->getMainMenuKeyboard());
            return;
        }

        $lines = ["📋 <b>Your Recent Orders</b>\n"];
        foreach ($orders as $order) {
            $lines[] = "• <code>{$order->order_number}</code> — " . strtoupper($order->status)
                . " — $" . number_format($order->total / 100, 2);
        }

        $this->sendReply($chatId, implode("\n", $lines), $this->getMainMenuKeyboard());
    }

    private function handleNewArrivals(int|string $chatId): void
    {
        $products = \App\Models\Product::with('primaryImage')
            ->where('status', 'published')
            ->latest()
            ->take(4)
            ->get();

        if ($products->isEmpty()) {
            $this->sendReply($chatId, "✨ No new arrivals right now. Check back soon!", $this->getMainMenuKeyboard());
            return;
        }

        $lines = ["✨ <b>Latest New Arrivals at LULU Couture</b>\n"];
        foreach ($products as $p) {
            $lines[] = "• <b>{$p->title}</b> — $" . number_format($p->base_price / 100, 2);
        }

        $lines[] = "\nShop online: " . config('app.url', 'http://localhost:8000');

        $this->sendReply($chatId, implode("\n", $lines), $this->getMainMenuKeyboard());
    }

    private function handleUnlink(int|string $chatId): void
    {
        $deleted = TelegramLink::where('telegram_chat_id', (string) $chatId)->delete();

        $this->sendReply($chatId,
            $deleted
                ? "🔗 Your account has been unlinked from this chat."
                : "You were not linked.",
            $this->getMainMenuKeyboard()
        );
    }

    private function sendReply(int|string $chatId, string $text, ?array $replyMarkup = null): void
    {
        app(\App\Services\TelegramService::class)->sendMessage($chatId, $text, $replyMarkup);
    }
}
