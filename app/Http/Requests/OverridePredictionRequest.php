<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OverridePredictionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        $prediction = $this->route('prediction');

        return [
            'selection' => $prediction
                ? $this->selectionRules($prediction->market_code)
                : ['required', 'string'],
            'probability' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    /**
     * Market-specific allowed selections — arbitrary values are rejected.
     */
    protected function selectionRules(string $marketCode): array
    {
        if ($marketCode === 'correct_score') {
            return ['required', 'string', 'regex:/^\d+-\d+$/'];
        }

        $allowed = [
            '1x2' => ['home', 'draw', 'away'],
            'draw' => ['draw'],
            'double_chance' => ['1x', 'x2', '12'],
            'over_1_5' => ['over_1_5', 'under_1_5'],
            'over_2_5' => ['over_2_5', 'under_2_5'],
            'btts' => ['yes', 'no'],
        ];

        return ['required', 'string', Rule::in($allowed[$marketCode] ?? [])];
    }
}
