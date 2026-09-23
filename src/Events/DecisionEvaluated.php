<?php

declare(strict_types=1);

namespace Priyanshu\LaravelJev\Events;

use Priyanshu\LaravelJev\Support\JevDecision;

class DecisionEvaluated
{
    public function __construct(
        public readonly string $input,
        public readonly string $criteriaOrType,
        public readonly JevDecision $decision,
        public readonly ?int $latencyMs = null
    ) {}
}
