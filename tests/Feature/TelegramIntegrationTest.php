<?php

namespace Tests\Feature;

use App\Events\OrderPlaced;
use App\Events\OrderStatusChanged;
use App\Jobs\BroadcastNewArrival;
use App\Jobs\SendTelegramMessage;
use App\Models\Order;
use App\Models\TelegramLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TelegramIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private function makeOwner(): User
    {
        return User::create([
            'name' => 'Owner',
            'email' => 'owner@lulu.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);
    }

    private function makeOrder(array $attrs = []): Order
    {
        return Order::create(array_merge([
            'order_number' => 'LULU-TEST01',
            'customer_name' => 'Test Customer',
            'customer_phone' => '+251911234567',
            'customer_address' => 'Test St',
            'customer_city' => 'Addis Ababa',
            'status' => 'pending',
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'subtotal' => 10000,
            'discount_amount' => 0,
            'total' => 10000,
        ], $attrs));
    }

    // ── Core Requirement: OrderPlaced → queued Telegram job ──────
    public function test_order_placed_event_dispatches_telegram_job_not_synchronously(): void
    {
        Queue::fake();
        config(['services.telegram.owner_chat_id' => '12345678']);

        $order = $this->makeOrder();
        $event = new \App\Events\OrderPlaced($order);

        // Invoke the listener directly (bypasses listener queue, tests inner job dispatch)
        $listener = new \App\Listeners\SendOrderPlacedNotification();
        $listener->handle($event);

        Queue::assertPushed(SendTelegramMessage::class, function ($job) {
            return $job->chatId == '12345678';
        });
    }

    // ── OrderStatusChanged → queued customer notification ────────
    public function test_order_status_changed_dispatches_telegram_job_to_customer(): void
    {
        Queue::fake();

        $order = $this->makeOrder();

        TelegramLink::create([
            'telegram_chat_id' => '99999999',
            'order_id' => $order->id,
            'phone_number' => $order->customer_phone,
        ]);

        $event = new \App\Events\OrderStatusChanged($order, 'pending', 'confirmed');

        // Invoke listener directly to verify it pushes the job
        $listener = new \App\Listeners\SendStatusChangedNotification();
        $listener->handle($event);

        Queue::assertPushed(SendTelegramMessage::class, function ($job) {
            return $job->chatId == '99999999';
        });
    }

    // ── No customer notification if no link exists ────────────────
    public function test_order_status_changed_does_not_dispatch_if_no_telegram_link(): void
    {
        Queue::fake();

        $order = $this->makeOrder(['order_number' => 'LULU-NOLINK']);
        // Intentionally no TelegramLink created

        OrderStatusChanged::dispatch($order, 'pending', 'confirmed');

        Queue::assertNotPushed(SendTelegramMessage::class);
    }

    // ── Broadcast queued correctly ────────────────────────────────
    public function test_broadcast_endpoint_queues_broadcast_job(): void
    {
        Queue::fake();
        $owner = $this->makeOwner();

        $this->actingAs($owner)->post('/admin/settings/telegram/broadcast', [
            'message' => '✨ New arrivals just dropped!'
        ]);

        Queue::assertPushed(BroadcastNewArrival::class, function ($job) {
            return $job->message === '✨ New arrivals just dropped!';
        });
    }

    // ── Webhook: /start command links an order ────────────────────
    public function test_telegram_webhook_start_command_links_by_order_number(): void
    {
        $order = $this->makeOrder();

        $payload = [
            'message' => [
                'chat' => ['id' => 42424242],
                'text' => '/start LULU-TEST01',
            ]
        ];

        $response = $this->withoutMiddleware()
            ->postJson('/telegram/webhook', $payload, [
                'X-Telegram-Bot-Api-Secret-Token' => config('services.telegram.webhook_secret', ''),
            ]);

        $response->assertStatus(200)->assertJson(['ok' => true]);
        $this->assertDatabaseHas('telegram_links', [
            'telegram_chat_id' => '42424242',
            'order_id' => $order->id,
        ]);
    }

    // ── Webhook: /track command works without auth ────────────────
    public function test_telegram_webhook_track_command_returns_200(): void
    {
        $payload = [
            'message' => [
                'chat' => ['id' => 55555555],
                'text' => '/track LULU-NOTFOUND',
            ]
        ];

        $response = $this->withoutMiddleware()
            ->postJson('/telegram/webhook', $payload);

        $response->assertStatus(200)->assertJson(['ok' => true]);
    }

    // ── Webhook: invalid secret is rejected ───────────────────────
    public function test_telegram_webhook_rejects_invalid_secret_token(): void
    {
        config(['services.telegram.webhook_secret' => 'my-real-secret']);

        $response = $this->postJson('/telegram/webhook', ['message' => []], [
            'X-Telegram-Bot-Api-Secret-Token' => 'wrong-secret',
        ]);

        $response->assertStatus(403);
    }
}
