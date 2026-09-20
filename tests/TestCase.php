<?php

declare(strict_types=1);

namespace Priyanshu\LaravelJev\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Priyanshu\LaravelJev\Facades\Jev;
use Priyanshu\LaravelJev\JevServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            JevServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Jev' => Jev::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('jev.api_key', 'test-api-key');
        $app['config']->set('jev.threshold', 0.80);
        $app['config']->set('cache.default', 'array');
    }
}
