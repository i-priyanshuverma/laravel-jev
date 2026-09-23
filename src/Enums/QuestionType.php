<?php

declare(strict_types=1);

namespace Priyanshu\LaravelJev\Enums;

enum QuestionType: string
{
    case Noul = 'noul';
    case Choice = 'choice';
    case Score = 'score';
}
