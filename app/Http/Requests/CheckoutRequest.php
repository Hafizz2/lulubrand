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

    public function rules(): array
    {
        $rules = [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'logistics_mode' => ['required', 'in:pickup,delivery_fixed,delivery_rider'],
            'customer_address' => ['required_if:logistics_mode,delivery_fixed,delivery_rider', 'nullable', 'string', 'max:500'],
            'customer_city' => ['nullable', 'string', 'max:100'],
            'google_maps_link' => ['nullable', 'string', 'max:500'],
            'preferred_date' => ['required', 'date'],
            'preferred_time' => ['required', 'string', 'max:100'],
            'payment_method' => ['required', 'in:cod,transfer'],
            'selected_bank_id' => ['required_if:payment_method,transfer', 'nullable', 'exists:bank_accounts,id'],
            'payment_proof' => ['required_if:payment_method,transfer', 'nullable', 'file', 'image', 'max:5120'], // Max 5MB
            'confirmed_transaction_id' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
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
