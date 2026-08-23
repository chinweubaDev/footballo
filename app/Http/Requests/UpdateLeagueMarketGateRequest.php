<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeagueMarketGateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'enabled' => ['boolean'],
            'min_probability' => ['nullable', 'integer', 'between:0,100'],
            'min_confidence' => ['nullable', 'integer', 'between:0,100'],
        ];
    }
}
