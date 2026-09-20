<?php

declare(strict_types=1);

namespace Priyanshu\LaravelJev\Tests\Feature;

use Priyanshu\LaravelJev\Facades\Jev;
use Priyanshu\LaravelJev\Tests\TestCase;

class JevFacadeTest extends TestCase
{
    public function test_facade_resolves_and_swaps_with_fake(): void
    {
        Jev::fake([
            'is:spam' => true,
            'choose'  => 'billing',
            'score:urgency' => 2.5,
        ]);

        $this->assertTrue(Jev::is('Special offer', 'spam'));
        $this->assertFalse(Jev::isNot('Special offer', 'spam'));
        $this->assertSame('billing', Jev::choose('Need invoice', ['billing', 'sales']));
        $this->assertSame(2.5, Jev::score('Urgent issue', 'urgency', ['low', 'medium', 'high']));

        Jev::assertChecked('spam');
        Jev::assertChosen('billing');
    }

    public function test_batch_analyze_via_fake(): void
    {
        Jev::fake([
            'choose:team' => 'sales',
        ]);

        $batchResult = Jev::analyze('Interested in enterprise plan')
            ->is('is_urgent')
            ->choose('team', ['billing', 'sales', 'support'])
            ->score('urgency')
            ->run();

        $this->assertSame('sales', $batchResult->choice('team'));
    }

    public function test_global_jev_helper(): void
    {
        Jev::fake([
            'is:spam' => true,
            'choose:team' => 'support',
        ]);

        $this->assertTrue(jev()->is('win money', 'spam'));

        $batch = jev('help with setup')
            ->choose('team', ['sales', 'support'])
            ->run();

        $this->assertSame('support', $batch->choice('team'));
    }
}
