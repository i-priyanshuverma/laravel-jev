<?php

declare(strict_types=1);

namespace Priyanshu\LaravelJev\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Priyanshu\LaravelJev\Enums\QuestionType;
use Priyanshu\LaravelJev\Support\Question;

class QuestionTest extends TestCase
{
    public function test_question_types_and_payload_serialization(): void
    {
        $noul = Question::noul('Is this spam?');
        $this->assertSame('noul', $noul->getType());
        $this->assertSame(QuestionType::Noul, $noul->getQuestionType());

        $choice = Question::choice('Select category', ['a', 'b']);
        $this->assertSame('choice', $choice->getType());
        $this->assertSame(QuestionType::Choice, $choice->getQuestionType());

        $score = Question::score('Rate urgency', ['low', 'high']);
        $this->assertSame('score', $score->getType());
        $this->assertSame(QuestionType::Score, $score->getQuestionType());

        $payload = $noul->toArray();
        $this->assertSame('noul', $payload['type']);
        $this->assertSame('Is this spam?', $payload['instructions']);
    }
}
