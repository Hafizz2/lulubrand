<?php

namespace App\Contracts;

use App\Models\Order;

interface PaymentMethodInterface
{
    /**
     * Process order payment.
     *
     * @param Order $order
     * @param array $payload
     * @return array ['success' => bool, 'transaction_id' => ?string, 'message' => ?string]
     */
    public function processPayment(Order $order, array $payload = []): array;
}
