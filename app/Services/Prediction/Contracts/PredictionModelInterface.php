<?php

namespace App\Services\Prediction\Contracts;

use App\Services\Prediction\Support\ModelPrediction;
use App\Services\Prediction\Support\PredictionContext;

interface PredictionModelInterface
{
    /**
     * Stable, machine-readable name of the model (matches ensemble weight keys).
     */
    public function name(): string;

    /**
     * Produce a 1X2 probability estimate (plus optional expected goals) from
     * the prepared context and features. Models must NOT call the API here.
     */
    public function predict(PredictionContext $context, array $features): ModelPrediction;
}
