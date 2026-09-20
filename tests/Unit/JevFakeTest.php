<?php

declare(strict_types=1);

namespace Priyanshu\LaravelJev\Tests\Unit;

use PHPUnit\Framework\ExpectationFailedException;
use Priyanshu\LaravelJev\Testing\JevFake;
use Priyanshu\LaravelJev\Tests\TestCase;

class JevFakeTest extends TestCase
{
    public function test_fake_records_and_asserts_boolean_checks(): void
    {
        $fake = new JevFake([
            'is:spam' => true,
        ]);

        $this->assertTrue($fake->is('buy crypto', 'spam'));
        $this->assertFalse($fake->isNot('buy crypto', 'spam'));

        $fake->assertChecked('spam');
        $fake->assertNotChecked('toxicity');
    }

    public function test_fake_records_and_asserts_choice_checks(): void
    {
        $fake = new JevFake([
            'choose' => 'technical',
        ]);

        $choice = $fake->choose('cannot connect to database', ['billing', 'technical', 'sales']);

        $this->assertSame('technical', $choice);
        $fake->assertChosen('technical');
    }

    public function test_fake_assert_nothing_classified_throws_when_calls_occurred(): void
    {
        $fake = new JevFake(['is:spam' => true]);
        $fake->is('hello', 'spam');

        $this->expectException(ExpectationFailedException::class);
        $fake->assertNothingClassified();
    }
}
