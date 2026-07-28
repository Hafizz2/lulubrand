<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SetTelegramWebhookRequest;
use App\Http\Requests\Admin\TelegramBroadcastRequest;
use App\Jobs\BroadcastNewArrival;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TelegramSettingsController extends Controller
{
    public function __construct(private TelegramService $telegram) {}

    public function index()
    {
        $botToken = config('services.telegram.bot_token');
        $configured = ! empty($botToken);

        $webhookInfo = [];
        $botInfo = [];

        if ($configured) {
            try {
                $webhookInfo = $this->telegram->getWebhookInfo();
                $botInfo = $this->telegram->getMe();
            } catch (\Exception) {
                // Silently fail if the bot is unreachable
            }
        }

        return Inertia::render('Settings/Telegram', [
            'configured' => $configured,
            'webhookInfo' => $webhookInfo,
            'botInfo' => $botInfo,
            'ownerChatId' => config('services.telegram.owner_chat_id'),
            'appUrl' => config('app.url'),
        ]);
    }

    public function setWebhook(SetTelegramWebhookRequest $request)
    {
        $validated = $request->validated();

        $secret = config('services.telegram.webhook_secret');
        $result = $this->telegram->setWebhook($validated['webhook_url'], $secret);

        if ($result['ok'] ?? false) {
            return back()->with('success', 'Webhook registered: ' . $validated['webhook_url']);
        }

        return back()->withErrors(['webhook' => $result['description'] ?? 'Failed to set webhook.']);
    }

    public function deleteWebhook()
    {
        $this->telegram->deleteWebhook();
        return back()->with('success', 'Webhook cleared.');
    }

    public function previewBroadcast(TelegramBroadcastRequest $request)
    {
        $validated = $request->validated();

        // Send preview to owner's own chat
        $ownerChatId = config('services.telegram.owner_chat_id');
        if (! $ownerChatId) {
            return back()->withErrors(['preview' => 'Owner chat_id not configured in .env (TELEGRAM_OWNER_CHAT_ID).']);
        }

        $preview = "👁️ <b>BROADCAST PREVIEW</b>\n\n" . $validated['message'];
        $this->telegram->sendMessage($ownerChatId, $preview);

        return back()->with('success', 'Preview sent to your Telegram. Approve to broadcast.');
    }

    public function broadcast(TelegramBroadcastRequest $request)
    {
        $validated = $request->validated();

        BroadcastNewArrival::dispatch($validated['message']);

        return back()->with('success', 'Broadcast queued! It will be sent to all subscribers in batches.');
    }
}
