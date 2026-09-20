<?php

declare(strict_types=1);

namespace Priyanshu\LaravelJev\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Priyanshu\LaravelJev\Facades\Jev;

class JevRule implements ValidationRule
{
    protected ?string $mustBe = null;
    protected ?string $mustNotBe = null;
    protected ?float $threshold = null;
    protected ?string $customMessage = null;

    public static function not(string $criteria, ?float $threshold = null): self
    {
        $rule = new self();
        $rule->mustNotBe = $criteria;
        $rule->threshold = $threshold;

        return $rule;
    }

    public static function is(string $criteria, ?float $threshold = null): self
    {
        $rule = new self();
        $rule->mustBe = $criteria;
        $rule->threshold = $threshold;

        return $rule;
    }

    public function message(string $message): self
    {
        $this->customMessage = $message;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '') {
            return;
        }

        if ($this->mustNotBe !== null) {
            if (Jev::is($value, $this->mustNotBe, $this->threshold)) {
                $msg = $this->customMessage ?? "The {$attribute} appears to be {$this->mustNotBe}.";
                $fail($msg);
            }
        }

        if ($this->mustBe !== null) {
            if (! Jev::is($value, $this->mustBe, $this->threshold)) {
                $msg = $this->customMessage ?? "The {$attribute} must be {$this->mustBe}.";
                $fail($msg);
            }
        }
    }
}
