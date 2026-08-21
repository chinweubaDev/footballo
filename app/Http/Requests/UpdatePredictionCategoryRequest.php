<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePredictionCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        return [
            'enabled' => ['nullable', 'boolean'],
            'min_confidence' => ['nullable', 'integer', 'min:0', 'max:100'],
            'min_probability' => ['nullable', 'integer', 'min:0', 'max:100'],
            'minimum_sample_size' => ['nullable', 'integer', 'min:1'],
            'homepage_enabled' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ];
    }
}
