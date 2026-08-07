<?php

namespace App\Http\Requests;

use App\Models\VerifiedTransaction;
use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->input('logistics_mode') === 'pickup') {
            $this->merge([
                'customer_country' => null,
                'customer_city' => null,
                'customer_district' => null,
                'customer_address' => null,
                'google_maps_link' => null,
            ]);
        }
    }

    public function rules(): array
    {
        $scheduleEnabled = \App\Models\SystemSetting::get('schedule_enabled', '0') === '1';

        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'logistics_mode' => ['required', 'in:pickup,delivery_fixed,delivery_rider'],
            'customer_country' => ['required_if:logistics_mode,delivery_fixed', 'nullable', 'string', 'max:100'],
            'customer_city' => ['required_if:customer_country,Ethiopia', 'nullable', 'string', 'max:100'],
            'customer_district' => ['nullable', 'string', 'max:100'],
            'customer_address' => ['required_if:logistics_mode,delivery_fixed,delivery_rider', 'nullable', 'string', 'max:500'],
            'google_maps_link' => ['nullable', 'string', 'max:500'],
            'preferred_date' => $scheduleEnabled ? ['required', 'date'] : ['nullable', 'date'],
            'preferred_time' => $scheduleEnabled ? ['required', 'string', 'max:100'] : ['nullable', 'string', 'max:100'],
            'payment_method' => ['required', 'in:cod,transfer,paypal,card'],
            'selected_bank_id' => ['required_if:payment_method,transfer', 'nullable', 'exists:bank_accounts,id'],
            'payment_proof' => ['required_if:payment_method,transfer', 'nullable', 'file', 'image', 'max:5120'], // Max 5MB
            'confirmed_transaction_id' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'redeem_points' => ['nullable', 'integer', 'min:0'],
        ];

        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $txId = trim($this->input('confirmed_transaction_id', ''));
            if ($txId !== '') {
                $exists = VerifiedTransaction::where('transaction_id', $txId)->exists();
                if ($exists) {
                    $validator->errors()->add('confirmed_transaction_id', 'This Transaction ID or receipt link has already been used for another order.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'customer_name.required' => 'Full name is required.',
            'customer_phone.required' => 'Phone number is required.',
            'logistics_mode.required' => 'Please select a delivery or pickup option.',
            'customer_address.required_if' => 'Delivery address is required when selecting delivery.',
            'preferred_date.required' => 'Preferred Date is required.',
            'preferred_time.required' => 'Preferred Time slot is required.',
            'payment_method.required' => 'Please select a payment method.',
            'selected_bank_id.required_if' => 'Please select the bank account you transferred to.',
            'payment_proof.required_if' => 'Payment proof screenshot is required for bank transfers.',
            'payment_proof.image' => 'Payment proof must be a valid image file (JPG, PNG, WebP).',
        ];
    }
}
