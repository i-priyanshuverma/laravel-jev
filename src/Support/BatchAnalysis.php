<?php

declare(strict_types=1);

namespace Priyanshu\LaravelJev\Support;

use Priyanshu\LaravelJev\Contracts\Jev as JevContract;

class BatchAnalysis
{
    /** @var array<string, Question> */
    protected array $questions = [];

    public function __construct(
        protected JevContract $manager,
        protected string $state
    ) {}

    /**
     * Add a boolean (noul) question.
     */
    public function is(string $name, ?string $instructions = null, array $criteria = []): self
    {
        $instruction = $instructions ?? "Is this text {$name}?";
        $this->questions[$name] = Question::noul($instruction, $criteria);

        return $this;
    }

    /**
     * Add a choice question.
     *
     * @param array<string, string>|list<string> $options
     */
    public function choose(string $name, array $options, ?string $instructions = null): self
    {
        $instruction = $instructions ?? "Select the best matching option for {$name}:";
        $this->questions[$name] = Question::choice($instruction, $options);

        return $this;
    }

    /**
     * Add a score question.
     *
     * @param list<string> $levels
     */
    public function score(string $name, array $levels = ['low', 'medium', 'high'], ?string $instructions = null): self
    {
        $instruction = $instructions ?? "Rate the level of {$name}:";
        $this->questions[$name] = Question::score($instruction, $levels);

        return $this;
    }

    /**
     * Execute the batch request.
     */
    public function run(): BatchResult
    {
        return $this->manager->runBatch($this->state, $this->questions);
    }
}
