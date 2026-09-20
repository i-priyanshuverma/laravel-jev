<?php

declare(strict_types=1);

namespace Priyanshu\LaravelJev\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Priyanshu\LaravelJev\Facades\Jev;

class JevNot implements ValidationRule
{
    public function __construct(
        protected string $criteria,
        protected ?float $threshold = null,
        protected ?string $customMessage = null
    ) {}

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '') {
            return;
        }

        if (Jev::is($value, $this->criteria, $this->threshold)) {
            $msg = $this->customMessage ?? "The {$attribute} appears to be {$this->criteria}.";
            $fail($msg);
        }
    }
}
