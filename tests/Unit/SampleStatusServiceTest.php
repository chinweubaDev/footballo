<?php

namespace Tests\Unit;

use App\Services\Prediction\Evaluation\SampleStatusService;
use Tests\TestCase;

class SampleStatusServiceTest extends TestCase
{
    public function test_four_tier_evidence_labels(): void
    {
        $service = new SampleStatusService();

        $this->assertSame('INSUFFICIENT', $service->label(0));
        $this->assertSame('INSUFFICIENT', $service->label(49));
        $this->assertSame('PRELIMINARY', $service->label(50));
        $this->assertSame('PRELIMINARY', $service->label(99));
        $this->assertSame('MEANINGFUL', $service->label(100));
        $this->assertSame('MEANINGFUL', $service->label(499));
        $this->assertSame('STRONGER EVIDENCE', $service->label(500));
        $this->assertSame('STRONGER EVIDENCE', $service->label(1000));
    }
}
