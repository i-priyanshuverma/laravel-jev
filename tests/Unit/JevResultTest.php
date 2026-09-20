<?php

declare(strict_types=1);

namespace Priyanshu\LaravelJev\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Priyanshu\LaravelJev\Exceptions\JevApiException;
use Priyanshu\LaravelJev\Support\JevResult;

class JevResultTest extends TestCase
{
    public function test_parses_noul_choice_and_score(): void
    {
        $payload = [
            'model' => 'jev-preview',
            'answers' => [
                'is_urgent' => [
                    'type' => 'noul',
                    'noul' => 0.88,
                    'confidence' => 0.88,
                ],
                'channel' => [
                    'type' => 'choice',
                    'choice' => 'email',
                    'confidence' => 0.94,
                    'probabilities' => ['email' => 0.94, 'slack' => 0.06],
                ],
                'sentiment' => [
                    'type' => 'score',
                    'score' => 1.8,
                    'confidence' => 0.91,
                    'nearestLevel' => 'positive',
                ],
            ],
            'usage' => [
                'total_tokens' => 30,
            ],
        ];

        $result = JevResult::fromArray($payload, 95);

        $this->assertSame('jev-preview', $result->model);
        $this->assertSame(95, $result->latencyMs);
        $this->assertTrue($result->has('is_urgent'));
        $this->assertFalse($result->has('non_existent'));

        $noul = $result->noul('is_urgent');
        $this->assertTrue($noul['isTrue']);
        $this->assertSame(0.88, $noul['probability']);
        $this->assertSame(0.88, $noul['confidence']);

        $choice = $result->choice('channel');
        $this->assertSame('email', $choice['choice']);
        $this->assertSame(0.94, $choice['confidence']);

        $score = $result->score('sentiment');
        $this->assertSame(1.8, $score['score']);
        $this->assertSame('positive', $score['nearestLevel']);

        $values = $result->values();
        $this->assertTrue($values['is_urgent']);
        $this->assertSame('email', $values['channel']);
        $this->assertSame(1.8, $values['sentiment']);
    }

    public function test_throws_on_missing_answer(): void
    {
        $result = JevResult::fromArray([
            'answers' => [
                'existing' => ['choice' => 'yes'],
            ],
        ]);

        $this->expectException(JevApiException::class);
        $this->expectExceptionMessage('Answer [missing] not found in Jev response');

        $result->get('missing');
    }
}
