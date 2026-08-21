<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateBacktestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        return [
            'league_id' => ['nullable', 'integer'],
            'season' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'date_start' => ['nullable', 'date'],
            'date_end' => ['nullable', 'date', 'after_or_equal:date_start'],
            'markets' => ['nullable', 'array'],
            'markets.*' => ['string', 'in:1x2,draw,double_chance,over_1_5,over_2_5,btts,correct_score'],
            'min_confidence' => ['nullable', 'integer', 'min:0', 'max:100'],
            'min_probability' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'model_version' => ['required', 'string', 'max:50'],
        ];
    }

    /**
     * The validated data normalized for BacktestRun creation.
     */
    public function normalized(): array
    {
        $data = $this->validated();

        return [
            'league_id' => isset($data['league_id']) ? (int) $data['league_id'] : null,
            'season' => isset($data['season']) ? (int) $data['season'] : null,
            'date_start' => $data['date_start'] ?? null,
            'date_end' => $data['date_end'] ?? null,
            'markets' => $data['markets'] ?? [],
            'min_confidence' => (int) ($data['min_confidence'] ?? 0),
            'min_probability' => (float) ($data['min_probability'] ?? 0),
            'model_version' => $data['model_version'],
        ];
    }
}
