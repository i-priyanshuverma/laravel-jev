<?php

declare(strict_types=1);

namespace Priyanshu\LaravelJev\Support;

class JevDecision
{
    public function __construct(
        protected mixed $value,
        protected float $confidence,
        protected string $type,
        protected array $probabilities = [],
        protected ?int $latencyMs = null,
        protected mixed $raw = null
    ) {}

    public function value(): mixed
    {
        return $this->value;
    }

    public function confidence(): float
    {
        return $this->confidence;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function probabilities(): array
    {
        return $this->probabilities;
    }

    public function latencyMs(): ?int
    {
        return $this->latencyMs;
    }

    public function raw(): mixed
    {
        return $this->raw;
    }

    public function passes(float $threshold = 0.80): bool
    {
        if ($this->type === 'noul') {
            return ($this->value === true) && ($this->confidence >= $threshold);
        }

        return $this->confidence >= $threshold;
    }

    public function isTrue(): bool
    {
        return $this->value === true;
    }

    public function isFalse(): bool
    {
        return $this->value === false;
    }

    public function __toString(): string
    {
        if (is_bool($this->value)) {
            return $this->value ? 'true' : 'false';
        }

        return (string) $this->value;
    }
}
