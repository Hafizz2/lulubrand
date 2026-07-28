<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreScheduleOverrideRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pickup_time_id' => ['nullable', 'exists:pickup_times,id'],
            'override_date' => ['required', 'date'],
            'status' => ['required', 'in:full,blocked'],
        ];
    }
}
