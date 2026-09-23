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

    public static function fromAnswer(array $raw, ?int $latencyMs = null): self
    {
        $type = (string) ($raw['type'] ?? (isset($raw['choice']) ? 'choice' : (isset($raw['score']) ? 'score' : 'noul')));

        if ($type === 'noul' || isset($raw['noul']) || isset($raw['probability'])) {
            $prob = (float) ($raw['probability'] ?? $raw['noul'] ?? 0.0);
            $conf = (float) ($raw['confidence'] ?? abs($prob - 0.5) * 2.0);

            return new self(
                value: $prob >= 0.5,
                confidence: $conf,
                type: 'noul',
                probabilities: ['true' => $prob, 'false' => 1.0 - $prob],
                latencyMs: $latencyMs,
                raw: $raw
            );
        }

        if ($type === 'choice' || isset($raw['choice'])) {
            return new self(
                value: (string) ($raw['choice'] ?? ''),
                confidence: (float) ($raw['confidence'] ?? 1.0),
                type: 'choice',
                probabilities: (array) ($raw['probabilities'] ?? []),
                latencyMs: $latencyMs,
                raw: $raw
            );
        }

        if ($type === 'score' || isset($raw['score'])) {
            return new self(
                value: (float) ($raw['score'] ?? 0.0),
                confidence: (float) ($raw['confidence'] ?? 1.0),
                type: 'score',
                probabilities: (array) ($raw['probabilities'] ?? []),
                latencyMs: $latencyMs,
                raw: $raw
            );
        }

        return new self(
            value: $raw['value'] ?? null,
            confidence: (float) ($raw['confidence'] ?? 1.0),
            type: $type,
            latencyMs: $latencyMs,
            raw: $raw
        );
    }

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
