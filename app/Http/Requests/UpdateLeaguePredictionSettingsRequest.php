<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeaguePredictionSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        return [
            'season' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'prediction_min_confidence' => ['nullable', 'integer', 'min:0', 'max:100'],
            'priority' => ['nullable', 'integer'],
            'prediction_enabled' => ['nullable', 'boolean'],
            'homepage_enabled' => ['nullable', 'boolean'],
            'auto_publish' => ['nullable', 'boolean'],
        ];
    }
}
