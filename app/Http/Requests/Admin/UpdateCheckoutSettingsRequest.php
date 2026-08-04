<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCheckoutSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'currency_symbol' => ['required', 'string', 'max:10'],
            'schedule_enabled' => ['required', 'in:0,1'],
            'logistics_pickup' => ['required', 'in:0,1'],
            'pickup_location_name' => ['nullable', 'string', 'max:255'],
            'pickup_location_link' => ['nullable', 'string', 'max:500'],
            'logistics_delivery_fixed' => ['required', 'in:0,1'],
            'delivery_fixed_fee' => ['nullable', 'numeric', 'min:0'],
            'logistics_delivery_rider' => ['required', 'in:0,1'],
            'rider_disclaimer' => ['nullable', 'string', 'max:1000'],
            'payment_cod' => ['required', 'in:0,1'],
            'payment_transfer' => ['required', 'in:0,1'],
            'payment_paypal' => ['required', 'in:0,1'],
            'paypal_instructions' => ['nullable', 'string', 'max:2000'],
            'payment_card' => ['required', 'in:0,1'],
            'card_instructions' => ['nullable', 'string', 'max:2000'],
            'deposit_required' => ['required', 'in:0,1'],
            'deposit_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'blocked_dates' => ['nullable', 'array'],
            'blocked_dates.*' => ['date'],
            'blocked_days_of_week' => ['nullable', 'array'],
            'blocked_days_of_week.*' => ['integer', 'min:0', 'max:6'],
        ];
    }
}
