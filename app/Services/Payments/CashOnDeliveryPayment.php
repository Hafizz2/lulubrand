<?php

namespace App\Services\Payments;

use App\Contracts\PaymentMethodInterface;
use App\Models\Order;

class CashOnDeliveryPayment implements PaymentMethodInterface
{
    public function processPayment(Order $order, array $payload = []): array
    {
        // COD requires payment on delivery
        $order->update([
            'payment_status' => 'unpaid',
            'notes' => trim(($order->notes ?? '') . ' [Payment: Cash on Delivery]'),
        ]);

        return [
            'success' => true,
            'transaction_id' => 'COD-' . strtoupper(uniqid()),
            'message' => 'Order registered for Cash on Delivery.',
        ];
    }
}
