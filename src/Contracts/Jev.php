<?php

declare(strict_types=1);

namespace Priyanshu\LaravelJev\Contracts;

use Priyanshu\LaravelJev\Support\BatchAnalysis;
use Priyanshu\LaravelJev\Support\BatchResult;
use Priyanshu\LaravelJev\Support\JevDecision;

interface Jev
{
    /**
     * Boolean classification: Does the input meet the criteria?
     */
    public function is(string $input, string $criteria, ?float $threshold = null): bool;

    /**
     * Inverse boolean check.
     */
    public function isNot(string $input, string $criteria, ?float $threshold = null): bool;

    /**
     * Categorical classification: Pick the single best option from the list.
     *
     * @param array<string, string>|list<string> $options
     */
    public function choose(string $input, array $options, ?string $default = null): string;

    /**
     * Rate input against ordered levels.
     *
     * @param list<string> $levels
     */
    public function score(string $input, string $criteria, array $levels = ['low', 'medium', 'high']): float;

    /**
     * Full evaluation returning a rich JevDecision object.
     */
    public function evaluate(string $input, string $criteria, ?float $threshold = null): JevDecision;

    /**
     * Fluent multi-question batching.
     */
    public function analyze(string $input): BatchAnalysis;

    /**
     * Execute batch questions through client.
     *
     * @param array<string, mixed> $questions
     */
    public function runBatch(string $state, array $questions): BatchResult;
}
