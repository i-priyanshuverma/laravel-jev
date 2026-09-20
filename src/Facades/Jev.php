<?php

declare(strict_types=1);

namespace Priyanshu\LaravelJev\Facades;

use Illuminate\Support\Facades\Facade;
use Priyanshu\LaravelJev\JevManager;
use Priyanshu\LaravelJev\Support\BatchAnalysis;
use Priyanshu\LaravelJev\Support\JevDecision;
use Priyanshu\LaravelJev\Testing\JevFake;

/**
 * @method static bool is(string $input, string $criteria, ?float $threshold = null)
 * @method static bool isNot(string $input, string $criteria, ?float $threshold = null)
 * @method static string choose(string $input, array $options, ?string $default = null)
 * @method static float score(string $input, string $criteria, array $levels = ['low', 'medium', 'high'])
 * @method static JevDecision evaluate(string $input, string $criteria, ?float $threshold = null)
 * @method static BatchAnalysis analyze(string $input)
 * @method static JevFake fake(array $expectations = [])
 * @method static bool isFaking()
 * @method static void assertChecked(string $criteria, ?callable $callback = null)
 * @method static void assertNotChecked(string $criteria)
 * @method static void assertChosen(string $option)
 * @method static void assertNothingClassified()
 *
 * @see \Priyanshu\LaravelJev\JevManager
 */
class Jev extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'jev';
    }
}
