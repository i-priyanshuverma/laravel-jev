<?php

declare(strict_types=1);

namespace Priyanshu\LaravelJev\Contracts;

use Priyanshu\LaravelJev\Support\JevResult;
use Priyanshu\LaravelJev\Support\Question;

interface ClientInterface
{
    /**
     * Send state and questions to TypeSafe Jev API.
     *
     * @param array<string, Question|array<string, mixed>> $questions
     */
    public function ask(string $state, array $questions): JevResult;
}
