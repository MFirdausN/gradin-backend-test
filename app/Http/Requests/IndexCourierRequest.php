<?php

namespace App\Http\Requests;

use App\Enums\CourierLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexCourierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('level'))) {
            $this->merge(['level' => array_map('trim', explode(',', $this->input('level')))]);
        }
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'level' => ['sometimes', 'array', 'min:1', 'max:5'],
            'level.*' => ['required', 'integer', Rule::enum(CourierLevel::class)],
            'sort' => ['sometimes', Rule::in(['name', 'created_at'])],
            'direction' => ['sometimes', Rule::in(['asc', 'desc'])],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'between:1,100'],
        ];
    }
}
