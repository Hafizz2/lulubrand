<?php

namespace App\Services\Payments;

use App\Contracts\PaymentMethodInterface;
use App\Models\Order;

class CardPayment implements PaymentMethodInterface
{
    public function processPayment(Order $order, array $payload = []): array
    {
        $order->update([
            'payment_status' => 'unpaid',
            'notes' => trim(($order->notes ?? '') . ' [Payment: Credit/Debit Card]'),
        ]);

        return [
            'success' => true,
            'transaction_id' => 'CARD-' . strtoupper(uniqid()),
            'message' => 'Card authorization request sent successfully.',
        ];
    }
}
