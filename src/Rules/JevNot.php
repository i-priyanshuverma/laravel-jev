<?php

declare(strict_types=1);

namespace Priyanshu\LaravelJev\Rules;

class JevNot extends JevRule
{
    public function __construct(
        string $criteria,
        ?float $threshold = null,
        ?string $customMessage = null
    ) {
        $this->mustNotBe = $criteria;
        $this->threshold = $threshold;
        $this->customMessage = $customMessage;
    }
}
