<?php

namespace App\Http\Requests;

use App\Enums\CourierLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9]{8,15}$/', Rule::unique('couriers', 'phone')],
            'email' => ['sometimes', 'nullable', 'email', 'max:255', Rule::unique('couriers', 'email')],
            'level' => ['required', 'integer', Rule::enum(CourierLevel::class)],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
