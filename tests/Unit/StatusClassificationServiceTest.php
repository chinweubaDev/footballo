<?php

namespace Tests\Unit;

use App\Services\Prediction\Validation\StatusClassificationService;
use Tests\TestCase;

class StatusClassificationServiceTest extends TestCase
{
    protected StatusClassificationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new StatusClassificationService();

        config()->set('evaluation.status_classification', [
            'minimum_sample' => 100,
            'strong_accuracy' => 62.0,
            'promising_accuracy' => 55.0,
            'weak_accuracy' => 50.0,
            'strong_brier_max' => 0.23,
        ]);
    }

    public function test_insufficient_data_below_minimum_sample(): void
    {
        $this->assertSame('INSUFFICIENT_DATA', $this->service->classify([
            'resolved' => 50,
            'accuracy' => 80.0,
            'brier_score' => 0.2,
        ]));
    }

    public function test_insufficient_data_when_accuracy_null(): void
    {
        $this->assertSame('INSUFFICIENT_DATA', $this->service->classify([
            'resolved' => 200,
            'accuracy' => null,
            'brier_score' => 0.2,
        ]));
    }

    public function test_weak_below_weak_threshold(): void
    {
        $this->assertSame('WEAK', $this->service->classify([
            'resolved' => 200,
            'accuracy' => 45.0,
            'brier_score' => 0.25,
        ]));
    }

    public function test_neutral_between_weak_and_promising(): void
    {
        $this->assertSame('NEUTRAL', $this->service->classify([
            'resolved' => 200,
            'accuracy' => 52.0,
            'brier_score' => 0.25,
        ]));
    }

    public function test_promising_between_promising_and_strong(): void
    {
        $this->assertSame('PROMISING', $this->service->classify([
            'resolved' => 200,
            'accuracy' => 58.0,
            'brier_score' => 0.22,
        ]));
    }

    public function test_strong_when_accuracy_and_brier_pass(): void
    {
        $this->assertSame('STRONG', $this->service->classify([
            'resolved' => 200,
            'accuracy' => 66.0,
            'brier_score' => 0.20,
        ]));
    }

    public function test_not_strong_when_brier_too_high(): void
    {
        // Accuracy passes the strong bar, but a poor Brier score demotes it.
        $this->assertSame('PROMISING', $this->service->classify([
            'resolved' => 200,
            'accuracy' => 66.0,
            'brier_score' => 0.30,
        ]));
    }

    public function test_with_status_adds_status_key(): void
    {
        $out = $this->service->withStatus([
            'resolved' => 200,
            'accuracy' => 66.0,
            'brier_score' => 0.20,
        ]);

        $this->assertSame('STRONG', $out['status']);
    }
}
