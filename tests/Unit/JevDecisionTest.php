<?php

declare(strict_types=1);

namespace Priyanshu\LaravelJev\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Priyanshu\LaravelJev\Support\JevDecision;

class JevDecisionTest extends TestCase
{
    public function test_passes_returns_true_when_boolean_is_true_and_above_threshold(): void
    {
        $decision = new JevDecision(
            value: true,
            confidence: 0.92,
            type: 'noul',
            probabilities: ['true' => 0.92, 'false' => 0.08]
        );

        $this->assertTrue($decision->passes(0.80));
        $this->assertTrue($decision->isTrue());
        $this->assertFalse($decision->isFalse());
        $this->assertSame(0.92, $decision->confidence());
        $this->assertSame('noul', $decision->type());
        $this->assertSame('true', (string) $decision);
    }

    public function test_passes_returns_false_when_confidence_below_threshold(): void
    {
        $decision = new JevDecision(
            value: true,
            confidence: 0.65,
            type: 'noul',
            probabilities: ['true' => 0.65, 'false' => 0.35]
        );

        $this->assertFalse($decision->passes(0.80));
        $this->assertTrue($decision->isTrue());
    }

    public function test_choice_decision_values(): void
    {
        $decision = new JevDecision(
            value: 'billing',
            confidence: 0.88,
            type: 'choice',
            probabilities: ['billing' => 0.88, 'support' => 0.12],
            latencyMs: 120
        );

        $this->assertSame('billing', $decision->value());
        $this->assertSame('billing', (string) $decision);
        $this->assertSame(120, $decision->latencyMs());
        $this->assertTrue($decision->passes(0.85));
        $this->assertFalse($decision->passes(0.90));
    }
}
