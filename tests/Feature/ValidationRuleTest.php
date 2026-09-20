<?php

declare(strict_types=1);

namespace Priyanshu\LaravelJev\Tests\Feature;

use Illuminate\Support\Facades\Validator;
use Priyanshu\LaravelJev\Facades\Jev;
use Priyanshu\LaravelJev\Rules\JevNot;
use Priyanshu\LaravelJev\Rules\JevRule;
use Priyanshu\LaravelJev\Tests\TestCase;

class ValidationRuleTest extends TestCase
{
    public function test_jev_not_rule_fails_when_input_meets_unwanted_criteria(): void
    {
        Jev::fake([
            'is:spam' => true,
        ]);

        $validator = Validator::make(
            ['comment' => 'Buy cheap bitcoins right now!'],
            ['comment' => [new JevNot('spam')]]
        );

        $this->assertTrue($validator->fails());
        $this->assertStringContainsString('spam', $validator->errors()->first('comment'));
    }

    public function test_jev_not_rule_passes_when_input_does_not_meet_criteria(): void
    {
        Jev::fake([
            'is:spam' => false,
        ]);

        $validator = Validator::make(
            ['comment' => 'Great product, loving the update.'],
            ['comment' => [new JevNot('spam')]]
        );

        $this->assertFalse($validator->fails());
    }

    public function test_fluent_jev_rule_helper(): void
    {
        Jev::fake([
            'is:toxic' => false,
            'is:english' => true,
        ]);

        $validator = Validator::make(
            ['feedback' => 'Love this app!'],
            [
                'feedback' => [
                    JevRule::not('toxic'),
                    JevRule::is('english'),
                ],
            ]
        );

        $this->assertFalse($validator->fails());
    }
}
