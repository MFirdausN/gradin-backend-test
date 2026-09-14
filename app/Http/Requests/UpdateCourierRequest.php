<?php

namespace App\Http\Requests;

use App\Enums\CourierLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['sometimes', 'required', 'string', 'max:20', 'regex:/^\+?[0-9]{8,15}$/', Rule::unique('couriers', 'phone')->ignore($this->route('courier'))],
            'email' => ['sometimes', 'nullable', 'email', 'max:255', Rule::unique('couriers', 'email')->ignore($this->route('courier'))],
            'level' => ['sometimes', 'required', 'integer', Rule::enum(CourierLevel::class)],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
