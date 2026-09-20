<?php

declare(strict_types=1);

namespace Priyanshu\LaravelJev\Support;

use Priyanshu\LaravelJev\Exceptions\JevApiException;

class JevResult
{
    /**
     * @param array<string, mixed> $answers
     * @param array<string, int> $usage
     */
    public function __construct(
        public readonly string $model,
        public readonly array $answers,
        public readonly array $usage = [],
        public readonly ?int $latencyMs = null
    ) {}

    public static function fromArray(array $payload, ?int $latencyMs = null): self
    {
        return new self(
            model: (string) ($payload['model'] ?? 'jev-default'),
            answers: (array) ($payload['answers'] ?? []),
            usage: (array) ($payload['usage'] ?? []),
            latencyMs: $latencyMs
        );
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->answers);
    }

    public function get(string $name): array
    {
        if (! $this->has($name)) {
            $available = implode(', ', array_keys($this->answers));
            throw new JevApiException("Answer [{$name}] not found in Jev response. Available: [{$available}].");
        }

        return (array) $this->answers[$name];
    }

    /**
     * Get boolean probability and resolution for a noul question.
     *
     * @return array{isTrue: bool, probability: float, confidence: float}
     */
    public function noul(string $name): array
    {
        $answer = $this->get($name);
        $prob = (float) ($answer['probability'] ?? $answer['noul'] ?? 0.0);
        $conf = (float) ($answer['confidence'] ?? abs($prob - 0.5) * 2.0);

        return [
            'isTrue' => $prob >= 0.5,
            'probability' => $prob,
            'confidence' => $conf,
        ];
    }

    /**
     * Get choice and confidence for a choice question.
     *
     * @return array{choice: string, confidence: float, probabilities: array}
     */
    public function choice(string $name): array
    {
        $answer = $this->get($name);
        $choice = (string) ($answer['choice'] ?? '');
        $conf = (float) ($answer['confidence'] ?? 1.0);
        $probs = (array) ($answer['probabilities'] ?? []);

        return [
            'choice' => $choice,
            'confidence' => $conf,
            'probabilities' => $probs,
        ];
    }

    /**
     * Get numerical rating and confidence for a score question.
     *
     * @return array{score: float, confidence: float, nearestLevel: ?string}
     */
    public function score(string $name): array
    {
        $answer = $this->get($name);
        $score = (float) ($answer['score'] ?? 0.0);
        $conf = (float) ($answer['confidence'] ?? 1.0);
        $nearest = isset($answer['nearestLevel']) ? (string) $answer['nearestLevel'] : null;

        return [
            'score' => $score,
            'confidence' => $conf,
            'nearestLevel' => $nearest,
        ];
    }

    /**
     * Map all answers to their fundamental scalar values.
     *
     * @return array<string, mixed>
     */
    public function values(): array
    {
        $results = [];

        foreach ($this->answers as $key => $answer) {
            if (isset($answer['choice'])) {
                $results[$key] = $answer['choice'];
            } elseif (isset($answer['score'])) {
                $results[$key] = $answer['score'];
            } elseif (isset($answer['noul']) || isset($answer['probability'])) {
                $prob = $answer['probability'] ?? $answer['noul'];
                $results[$key] = $prob >= 0.5;
            } else {
                $results[$key] = $answer['value'] ?? null;
            }
        }

        return $results;
    }
}
