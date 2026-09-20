<?php

declare(strict_types=1);

namespace Priyanshu\LaravelJev\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Priyanshu\LaravelJev\Client\JevClient;
use Priyanshu\LaravelJev\Exceptions\JevApiException;
use Priyanshu\LaravelJev\Support\Question;
use Priyanshu\LaravelJev\Tests\TestCase;

class DirectHttpClientTest extends TestCase
{
    public function test_sends_correct_http_payload_and_parses_response(): void
    {
        Http::fake([
            'api.typesafe.ai/v1/decide' => Http::response([
                'model' => 'jev-1.0',
                'answers' => [
                    'spam' => [
                        'type' => 'noul',
                        'noul' => 0.95,
                        'confidence' => 0.95,
                    ],
                    'department' => [
                        'type' => 'choice',
                        'choice' => 'billing',
                        'confidence' => 0.89,
                        'probabilities' => ['billing' => 0.89, 'tech' => 0.11],
                    ],
                    'urgency' => [
                        'type' => 'score',
                        'score' => 2.4,
                        'confidence' => 0.85,
                        'nearestLevel' => 'high',
                    ],
                ],
                'usage' => [
                    'input_tokens' => 20,
                    'output_tokens' => 5,
                    'total_tokens' => 25,
                ],
            ], 200),
        ]);

        $client = new JevClient(
            apiKey: 'test-api-key',
            baseUrl: 'https://api.typesafe.ai/v1'
        );

        $result = $client->ask('I want a refund for my last invoice', [
            'spam' => Question::noul('Is this spam?'),
            'department' => Question::choice('Which department?', ['billing', 'tech']),
            'urgency' => Question::score('Urgency', ['low', 'medium', 'high']),
        ]);

        $this->assertSame('jev-1.0', $result->model);
        $this->assertTrue($result->has('spam'));
        $this->assertTrue($result->noul('spam')['isTrue']);
        $this->assertSame('billing', $result->choice('department')['choice']);
        $this->assertSame(2.4, $result->score('urgency')['score']);
        $this->assertSame('high', $result->score('urgency')['nearestLevel']);

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Bearer test-api-key')
                && $request->url() === 'https://api.typesafe.ai/v1/decide'
                && $request['state'] === 'I want a refund for my last invoice'
                && isset($request['questions']['spam'])
                && $request['questions']['spam']['type'] === 'noul';
        });
    }

    public function test_throws_exception_on_api_error(): void
    {
        Http::fake([
            'api.typesafe.ai/v1/decide' => Http::response([
                'error' => 'Unauthorized key',
            ], 401),
        ]);

        $client = new JevClient('invalid-key');

        $this->expectException(JevApiException::class);
        $this->expectExceptionCode(401);

        $client->ask('Hello', [
            'spam' => Question::noul('Is this spam?'),
        ]);
    }
}
