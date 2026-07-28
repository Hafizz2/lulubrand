<?php

namespace App\Services;

use App\Models\NotificationLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private string $token;
    private string $baseUrl;

    public function __construct()
    {
        $this->token = config('services.telegram.bot_token', '');
        $this->baseUrl = "https://api.telegram.org/bot{$this->token}";
    }

    /**
     * Send a message to a specific Telegram chat_id.
     */
    public function sendMessage(string|int $chatId, string $text, ?array $replyMarkup = null, string $parseMode = 'HTML'): bool
    {
        if (empty($this->token)) {
            Log::warning('Telegram bot token not configured.');
            return false;
        }

        try {
            $payload = [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => $parseMode,
            ];

            if ($replyMarkup) {
                $payload['reply_markup'] = $replyMarkup;
            }

            $response = Http::timeout(10)->post("{$this->baseUrl}/sendMessage", $payload);

            $success = $response->successful() && ($response->json('ok') === true);

            NotificationLog::create([
                'recipient' => (string) $chatId,
                'channel' => 'telegram',
                'event_type' => 'send_message',
                'message_body' => $text,
                'status' => $success ? 'sent' : 'failed',
                'error_details' => $success ? null : $response->body(),
                'sent_at' => now(),
            ]);

            return $success;
        } catch (\Exception $e) {
            Log::error('Telegram sendMessage failed', ['error' => $e->getMessage(), 'chat_id' => $chatId]);

            NotificationLog::create([
                'recipient' => (string) $chatId,
                'channel' => 'telegram',
                'event_type' => 'send_message',
                'message_body' => $text,
                'status' => 'failed',
                'error_details' => $e->getMessage(),
                'sent_at' => now(),
            ]);

            return false;
        }
    }

    /**
     * Set the webhook URL for this bot.
     */
    public function setWebhook(string $url, string $secretToken): array
    {
        $response = Http::post("{$this->baseUrl}/setWebhook", [
            'url' => $url,
            'secret_token' => $secretToken,
        ]);

        return $response->json();
    }

    /**
     * Delete / clear the current webhook.
     */
    public function deleteWebhook(): array
    {
        $response = Http::post("{$this->baseUrl}/deleteWebhook");
        return $response->json();
    }

    /**
     * Get info on the currently set webhook.
     */
    public function getWebhookInfo(): array
    {
        $response = Http::get("{$this->baseUrl}/getWebhookInfo");
        return $response->json();
    }

    /**
     * Get the bot's own info.
     */
    public function getMe(): array
    {
        $response = Http::get("{$this->baseUrl}/getMe");
        return $response->json();
    }
}
