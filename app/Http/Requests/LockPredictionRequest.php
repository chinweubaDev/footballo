<?php

namespace App\Http\Requests;

use App\Models\Prediction;
use Illuminate\Foundation\Http\FormRequest;

class LockPredictionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $prediction = $this->route('prediction');

        return $prediction instanceof Prediction && $this->user()?->can('lock', $prediction);
    }

    public function rules(): array
    {
        return [];
    }
}
