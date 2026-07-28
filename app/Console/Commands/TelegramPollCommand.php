<?php

namespace App\Console\Commands;

use App\Http\Controllers\TelegramWebhookController;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TelegramPollCommand extends Command
{
    protected $signature = 'telegram:poll';
    protected $description = 'Poll Telegram API locally for development and route updates to TelegramWebhookController';

    public function handle()
    {
        $token = config('services.telegram.bot_token');
        if (empty($token)) {
            $this->error('TELEGRAM_BOT_TOKEN is not configured in .env');
            return 1;
        }

        $botUsername = config('services.telegram.bot_username', 'shoplulu_bot');
        $this->info("🤖 Telegram Polling Worker active for @{$botUsername}");
        $this->info("Listening for messages, inline buttons, and commands... (Press Ctrl+C to stop)");

        // Delete existing webhook so getUpdates polling works
        Http::get("https://api.telegram.org/bot{$token}/deleteWebhook");

        $offset = 0;
        $controller = app(TelegramWebhookController::class);

        while (true) {
            try {
                $response = Http::timeout(10)->get("https://api.telegram.org/bot{$token}/getUpdates", [
                    'offset' => $offset,
                    'timeout' => 5,
                ]);

                if ($response->successful() && $response->json('ok')) {
                    $updates = $response->json('result', []);
                    foreach ($updates as $update) {
                        $offset = $update['update_id'] + 1;

                        // Simulate Request to Webhook Controller
                        $request = new Request([], [], [], [], [], [], json_encode($update));
                        $request->headers->set('Content-Type', 'application/json');

                        $controller->handle($request);
                        $this->line("⚡ Processed update ID #{$update['update_id']}");
                    }
                }
            } catch (\Exception $e) {
                $this->warn("Polling warning: " . $e->getMessage());
                sleep(2);
            }
        }
    }
}
