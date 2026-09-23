<?php

declare(strict_types=1);

namespace Priyanshu\LaravelJev\Support;

use Priyanshu\LaravelJev\Enums\QuestionType;

class Question
{
    public function __construct(
        protected QuestionType|string $type,
        protected string $instructions,
        protected array $payload = []
    ) {}

    /**
     * Create a boolean (noul) question.
     *
     * @param array{true?: string, false?: string} $criteria
     */
    public static function noul(string $instructions, array $criteria = []): self
    {
        return new self(QuestionType::Noul, $instructions, [
            'criteria' => $criteria,
        ]);
    }

    /**
     * Create a choice question selecting one from predefined options.
     *
     * @param array<string, string>|list<string> $options
     */
    public static function choice(string $instructions, array $options): self
    {
        $normalized = [];
        foreach ($options as $key => $val) {
            if (is_int($key)) {
                $normalized[(string) $val] = (string) $val;
            } else {
                $normalized[(string) $key] = (string) $val;
            }
        }

        return new self(QuestionType::Choice, $instructions, [
            'options' => $normalized,
        ]);
    }

    /**
     * Create an ordered rating / score question.
     *
     * @param list<string> $levels
     */
    public static function score(string $instructions, array $levels): self
    {
        return new self(QuestionType::Score, $instructions, [
            'levels' => array_values($levels),
        ]);
    }

    public function getType(): string
    {
        return $this->type instanceof QuestionType ? $this->type->value : $this->type;
    }

    public function getQuestionType(): ?QuestionType
    {
        return $this->type instanceof QuestionType ? $this->type : QuestionType::tryFrom($this->type);
    }

    public function getInstructions(): string
    {
        return $this->instructions;
    }

    public function toArray(): array
    {
        return array_merge([
            'type' => $this->getType(),
            'instructions' => $this->instructions,
        ], $this->payload);
    }
}
