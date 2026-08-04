<?php

namespace App\Services\Payments;

use App\Contracts\PaymentMethodInterface;
use App\Models\Order;

class PaypalPayment implements PaymentMethodInterface
{
    public function processPayment(Order $order, array $payload = []): array
    {
        $order->update([
            'payment_status' => 'unpaid',
            'notes' => trim(($order->notes ?? '') . ' [Payment: PayPal Redirect]'),
        ]);

        return [
            'success' => true,
            'transaction_id' => 'PAYPAL-' . strtoupper(uniqid()),
            'message' => 'Redirected to PayPal sandbox environment.',
        ];
    }
}
