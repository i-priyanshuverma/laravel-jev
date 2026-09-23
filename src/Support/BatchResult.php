<?php

declare(strict_types=1);

namespace Priyanshu\LaravelJev\Support;

class BatchResult
{
    public function __construct(
        protected JevResult $rawResult,
        protected float $defaultThreshold = 0.80,
        protected ?int $latencyMs = null
    ) {}

    /**
     * Check if a boolean question resolved to true above the threshold.
     */
    public function is(string $name, ?float $threshold = null): bool
    {
        $limit = $threshold ?? $this->defaultThreshold;
        $noul = $this->rawResult->noul($name);

        return $noul['isTrue'] && ($noul['confidence'] >= $limit);
    }

    /**
     * Get the selected choice string.
     */
    public function choice(string $name): string
    {
        return $this->rawResult->choice($name)['choice'];
    }

    /**
     * Get the numeric score.
     */
    public function score(string $name): float
    {
        return $this->rawResult->score($name)['score'];
    }

    /**
     * Get nearest score level description if provided.
     */
    public function nearestLevel(string $name): ?string
    {
        return $this->rawResult->score($name)['nearestLevel'];
    }

    /**
     * Get the confidence of an answer.
     */
    public function confidence(string $name): float
    {
        $answer = $this->rawResult->get($name);

        return (float) ($answer['confidence'] ?? 1.0);
    }

    /**
     * Wrap an answer into a unified JevDecision object.
     */
    public function decision(string $name): JevDecision
    {
        return JevDecision::fromAnswer($this->rawResult->get($name), $this->latencyMs);
    }

    public function raw(): JevResult
    {
        return $this->rawResult;
    }

    public function latencyMs(): ?int
    {
        return $this->latencyMs;
    }
}
