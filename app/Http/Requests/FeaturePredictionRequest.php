<?php

namespace App\Http\Requests;

use App\Models\Prediction;
use Illuminate\Foundation\Http\FormRequest;

class FeaturePredictionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $prediction = $this->route('prediction');

        return $prediction instanceof Prediction && $this->user()?->can('feature', $prediction);
    }

    public function rules(): array
    {
        return [
            'featured' => ['nullable', 'boolean'],
            'admin_featured' => ['nullable', 'boolean'],
            'featured_priority' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
