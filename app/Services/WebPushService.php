<?php

namespace App\Services;

use App\Models\PushSubscription;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushService
{
    protected ?WebPush $webPush = null;

    public function __construct()
    {
        $publicKey = config('services.vapid.public_key') ?: env('VAPID_PUBLIC_KEY');
        $privateKey = config('services.vapid.private_key') ?: env('VAPID_PRIVATE_KEY');
        $subject = config('services.vapid.subject', config('app.url', 'http://localhost'));

        if (!empty($publicKey) && !empty($privateKey)) {
            try {
                $auth = [
                    'VAPID' => [
                        'subject'    => $subject,
                        'publicKey'  => $publicKey,
                        'privateKey' => $privateKey,
                    ],
                ];
                $this->webPush = new WebPush($auth);
            } catch (\Throwable $e) {
                Log::error("Failed to initialize WebPush client", ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Send Web Push notification to a single PushSubscription record.
     */
    public function sendToSubscription(PushSubscription $sub, string $title, string $body, ?string $url = null, ?string $icon = null): bool
    {
        if (!$this->webPush) {
            Log::warning("WebPush not configured with VAPID keys. Notification skipped.");
            return false;
        }

        try {
            $subscription = Subscription::create([
                'endpoint'        => $sub->endpoint,
                'publicKey'       => $sub->public_key,
                'authToken'       => $sub->auth_token,
                'contentEncoding' => $sub->content_encoding ?? 'aesgcm',
            ]);

            $payload = json_encode([
                'title' => $title,
                'body'  => $body,
                'icon'  => $icon ?: asset('logo.png'),
                'url'   => $url ?: url('/'),
                'badge' => asset('logo.png'),
            ]);

            $report = $this->webPush->sendOneNotification($subscription, $payload);

            if ($report->isSuccess()) {
                Log::info("WebPush notification delivered successfully", ['endpoint' => $sub->endpoint]);
                return true;
            }

            if ($report->isSubscriptionExpired()) {
                Log::info("WebPush subscription expired, removing", ['endpoint' => $sub->endpoint]);
                $sub->delete();
            } else {
                Log::error("WebPush send failed", ['reason' => $report->getReason()]);
            }

            return false;
        } catch (\Throwable $e) {
            Log::error("Exception sending WebPush", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send Web Push notification to a specific user (all their active browser subscriptions).
     */
    public function sendToUser(int $userId, string $title, string $body, ?string $url = null, ?string $icon = null): int
    {
        $subscriptions = PushSubscription::where('user_id', $userId)->get();
        $sentCount = 0;

        foreach ($subscriptions as $sub) {
            if ($this->sendToSubscription($sub, $title, $body, $url, $icon)) {
                $sentCount++;
            }
        }

        return $sentCount;
    }

    /**
     * Broadcast Web Push notification to target audience ('all' or 'customers').
     */
    public function broadcast(string $target, string $title, string $body, ?string $url = null, ?string $icon = null): int
    {
        $query = PushSubscription::query();

        if ($target === 'customers') {
            $query->whereNotNull('user_id');
        }

        $subscriptions = $query->get();
        $sentCount = 0;

        foreach ($subscriptions as $sub) {
            if ($this->sendToSubscription($sub, $title, $body, $url, $icon)) {
                $sentCount++;
            }
        }

        return $sentCount;
    }
}
