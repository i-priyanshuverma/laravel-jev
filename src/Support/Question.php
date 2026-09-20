<?php

declare(strict_types=1);

namespace Priyanshu\LaravelJev\Support;

class Question
{
    public function __construct(
        protected string $type,
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
        return new self('noul', $instructions, [
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

        return new self('choice', $instructions, [
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
        return new self('score', $instructions, [
            'levels' => array_values($levels),
        ]);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getInstructions(): string
    {
        return $this->instructions;
    }

    public function toArray(): array
    {
        return array_merge([
            'type' => $this->type,
            'instructions' => $this->instructions,
        ], $this->payload);
    }
}
