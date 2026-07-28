<?php

namespace App\Services\Payments;

use App\Contracts\PaymentMethodInterface;
use App\Models\Order;

class ManualMobileMoneyPayment implements PaymentMethodInterface
{
    public function processPayment(Order $order, array $payload = []): array
    {
        $reference = $payload['mobile_money_reference'] ?? 'PENDING-MANUAL';

        $order->update([
            'payment_status' => 'unpaid',
            'notes' => trim(($order->notes ?? '') . " [Payment: Mobile Money Ref: {$reference}]"),
        ]);

        return [
            'success' => true,
            'transaction_id' => 'MM-' . $reference,
            'message' => 'Order registered with Mobile Money reference.',
        ];
    }
}
