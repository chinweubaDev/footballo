<?php

namespace App\Http\Requests;

use App\Models\Prediction;
use Illuminate\Foundation\Http\FormRequest;

class PublishPredictionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $prediction = $this->route('prediction');

        return $prediction instanceof Prediction && $this->user()?->can('publish', $prediction);
    }

    public function rules(): array
    {
        return [];
    }
}
