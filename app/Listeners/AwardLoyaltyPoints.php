<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Jobs\SendTelegramMessage;
use App\Models\TelegramLink;
use App\Services\LoyaltyService;
use Illuminate\Contracts\Queue\ShouldQueue;

class AwardLoyaltyPoints implements ShouldQueue
{
    public function __construct(
        protected LoyaltyService $loyaltyService
    ) {}

    public function handle(OrderPlaced $event): void
    {
        $order = $event->order;

        if (!$order->user_id) {
            return;
        }

        $user = $order->user;

        // Ensure user is loaded
        if (!$user) {
            return;
        }

        $transaction = $this->loyaltyService->earnPoints(
            user: $user,
            purchaseAmountCents: $order->total,
            source: 'online_order',
            referenceId: $order->id,
            referenceType: 'order'
        );

        if ($transaction) {
            $order->update(['loyalty_points_earned' => $transaction->points]);

            // Send Telegram notification if user has telegram_chat_id linked
            $link = TelegramLink::where('user_id', $user->id)
                ->orWhere('order_id', $order->id)
                ->first();

            if ($link && $link->telegram_chat_id) {
                $message = "🎉 <b>You've earned {$transaction->points} Loyalty Points!</b>\n\n"
                    . "Thank you for your order <code>#{$order->order_number}</code>.\n"
                    . "Your points have been added to your balance.";
                    
                SendTelegramMessage::dispatch($link->telegram_chat_id, $message);
            }

            // Dispatch SMS notification via AfroMessage if user has a phone number
            if ($user->phone) {
                $totalPoints = $this->loyaltyService->getBalance($user);
                $smsMessage = "Hey {$user->name}, thank you for your purchase from LULU! You got {$transaction->points} LULU points, and your total points are {$totalPoints} points.";
                \App\Jobs\SendSmsNotification::dispatch($user->phone, $smsMessage);
            }

            // Dispatch Web Push notification
            $pushTitle = "🎉 Earned +{$transaction->points} LULU Points!";
            $pushBody  = "Thank you for your order! You now have " . $this->loyaltyService->getBalance($user) . " points.";
            \App\Jobs\SendWebPushNotification::dispatch($user->id, $pushTitle, $pushBody, url('/cart'));
        }
    }
}
