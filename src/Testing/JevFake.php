<?php

declare(strict_types=1);

namespace Priyanshu\LaravelJev\Testing;

use PHPUnit\Framework\Assert as PHPUnit;
use Priyanshu\LaravelJev\Contracts\Jev as JevContract;
use Priyanshu\LaravelJev\Support\BatchAnalysis;
use Priyanshu\LaravelJev\Support\BatchResult;
use Priyanshu\LaravelJev\Support\JevDecision;
use Priyanshu\LaravelJev\Support\JevResult;

class JevFake implements JevContract
{
    /** @var array<string, mixed> */
    protected array $expectations = [];

    /** @var list<array{input: string, criteria: string, result: bool}> */
    protected array $recordedIs = [];

    /** @var list<array{input: string, options: array, choice: string}> */
    protected array $recordedChoose = [];

    /** @var list<array{input: string, criteria: string, levels: array, score: float}> */
    protected array $recordedScore = [];

    /** @var list<array{input: string, questions: array}> */
    protected array $recordedBatch = [];

    public function __construct(array $expectations = [])
    {
        $this->expectations = $expectations;
    }

    public function is(string $input, string $criteria, ?float $threshold = null): bool
    {
        $key = "is:{$criteria}";
        $resolved = $this->resolveValue($key, true);

        $this->recordedIs[] = [
            'input' => $input,
            'criteria' => $criteria,
            'result' => (bool) $resolved,
        ];

        return (bool) $resolved;
    }

    public function isNot(string $input, string $criteria, ?float $threshold = null): bool
    {
        return ! $this->is($input, $criteria, $threshold);
    }

    public function choose(string $input, array $options, ?string $default = null): string
    {
        $resolved = $this->resolveValue('choose', $default ?? (reset($options) ?: ''));

        $this->recordedChoose[] = [
            'input' => $input,
            'options' => $options,
            'choice' => (string) $resolved,
        ];

        return (string) $resolved;
    }

    public function score(string $input, string $criteria, array $levels = ['low', 'medium', 'high']): float
    {
        $key = "score:{$criteria}";
        $resolved = (float) $this->resolveValue($key, (float) $this->resolveValue('score', 1.0));

        $this->recordedScore[] = [
            'input' => $input,
            'criteria' => $criteria,
            'levels' => $levels,
            'score' => $resolved,
        ];

        return $resolved;
    }

    public function evaluate(string $input, string $criteria, ?float $threshold = null): JevDecision
    {
        $isTrue = $this->is($input, $criteria, $threshold);

        return new JevDecision(
            value: $isTrue,
            confidence: 0.95,
            type: 'noul',
            probabilities: ['true' => $isTrue ? 0.95 : 0.05, 'false' => $isTrue ? 0.05 : 0.95],
            latencyMs: 1
        );
    }

    public function analyze(string $input): BatchAnalysis
    {
        return new BatchAnalysis($this, $input);
    }

    public function runBatch(string $state, array $questions): BatchResult
    {
        $this->recordedBatch[] = [
            'input' => $state,
            'questions' => $questions,
        ];

        $answers = [];
        foreach ($questions as $name => $question) {
            $expectedChoice = $this->resolveValue("choose:{$name}", $this->resolveValue('choose', 'default'));
            $expectedIs = $this->resolveValue("is:{$name}", true);
            $expectedScore = (float) $this->resolveValue("score:{$name}", 1.0);

            $answers[$name] = [
                'type' => 'choice',
                'choice' => (string) $expectedChoice,
                'confidence' => 0.95,
                'probabilities' => [(string) $expectedChoice => 0.95],
            ];
        }

        $rawResult = new JevResult(
            model: 'jev-fake',
            answers: $answers,
            usage: ['total_tokens' => 20],
            latencyMs: 1
        );

        return new BatchResult($rawResult, 0.80, 1);
    }

    public function assertChecked(string $criteria, ?callable $callback = null): void
    {
        $matched = array_filter($this->recordedIs, function ($record) use ($criteria, $callback) {
            if ($record['criteria'] !== $criteria) {
                return false;
            }

            return $callback ? $callback($record['input'], $record['result']) : true;
        });

        PHPUnit::assertTrue(
            count($matched) > 0,
            "Failed asserting that criteria [{$criteria}] was checked with Jev."
        );
    }

    public function assertNotChecked(string $criteria): void
    {
        $matched = array_filter($this->recordedIs, fn ($r) => $r['criteria'] === $criteria);

        PHPUnit::assertCount(
            0,
            $matched,
            "Failed asserting that criteria [{$criteria}] was not checked with Jev."
        );
    }

    public function assertChosen(string $option): void
    {
        $matched = array_filter($this->recordedChoose, fn ($r) => $r['choice'] === $option);

        PHPUnit::assertTrue(
            count($matched) > 0,
            "Failed asserting that option [{$option}] was chosen with Jev."
        );
    }

    public function assertNothingClassified(): void
    {
        PHPUnit::assertEmpty($this->recordedIs, 'Expected no boolean checks, but some occurred.');
        PHPUnit::assertEmpty($this->recordedChoose, 'Expected no choices, but some occurred.');
        PHPUnit::assertEmpty($this->recordedScore, 'Expected no scores, but some occurred.');
    }

    protected function resolveValue(string $key, mixed $fallback): mixed
    {
        if (array_key_exists($key, $this->expectations)) {
            $val = $this->expectations[$key];
            return is_callable($val) ? $val() : $val;
        }

        return $fallback;
    }
}
