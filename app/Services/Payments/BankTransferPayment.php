<?php

namespace App\Services\Payments;

use App\Contracts\PaymentMethodInterface;
use App\Models\Order;

class BankTransferPayment implements PaymentMethodInterface
{
    public function processPayment(Order $order, array $payload = []): array
    {
        $order->update([
            'payment_status' => 'unpaid',
            'notes' => trim(($order->notes ?? '') . ' [Payment: Bank Transfer]'),
        ]);

        return [
            'success' => true,
            'transaction_id' => $order->confirmed_transaction_id ?? ('TRANSFER-' . strtoupper(uniqid())),
            'message' => 'Bank Transfer payment details registered.',
        ];
    }
}
