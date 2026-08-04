<?php

namespace App\Services\Payments;

use App\Contracts\PaymentMethodInterface;
use InvalidArgumentException;

class PaymentGatewayManager
{
    public function driver(string $method): PaymentMethodInterface
    {
        return match ($method) {
            'cod' => new CashOnDeliveryPayment(),
            'transfer', 'bank_transfer' => new BankTransferPayment(),
            'manual_mobile_money' => new ManualMobileMoneyPayment(),
            'paypal' => new PaypalPayment(),
            'card' => new CardPayment(),
            default => throw new InvalidArgumentException("Unsupported payment method: {$method}"),
        };
    }
}
