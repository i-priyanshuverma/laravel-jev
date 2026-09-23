<?php

declare(strict_types=1);

namespace Priyanshu\LaravelJev\Tests\Feature;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Priyanshu\LaravelJev\Events\DecisionEvaluated;
use Priyanshu\LaravelJev\Facades\Jev;
use Priyanshu\LaravelJev\Tests\TestCase;

class DecisionEvaluatedEventTest extends TestCase
{
    public function test_decision_evaluated_event_is_dispatched(): void
    {
        Event::fake([DecisionEvaluated::class]);

        config()->set('jev.api_key', 'test_key');

        Http::fake([
            'api.typesafe.ai/v1/decide' => Http::response([
                'model' => 'jev-preview',
                'answers' => [
                    'noul' => [
                        'type' => 'noul',
                        'probability' => 0.94,
                        'confidence' => 0.88,
                    ],
                ],
                'usage' => ['total_tokens' => 15],
            ], 200),
        ]);

        $decision = Jev::evaluate('Click here for free bitcoin', 'spam');

        $this->assertTrue($decision->isTrue());

        Event::assertDispatched(DecisionEvaluated::class, function (DecisionEvaluated $event) {
            return $event->input === 'Click here for free bitcoin'
                && $event->criteriaOrType === 'spam'
                && $event->decision->isTrue();
        });
    }
}
