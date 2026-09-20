<?php

declare(strict_types=1);

namespace Priyanshu\LaravelJev;

use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rule;
use Priyanshu\LaravelJev\Rules\JevRule;

class JevServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/jev.php', 'jev');

        $this->app->singleton('jev', function ($app) {
            return new JevManager($app);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/jev.php' => $this->app->configPath('jev.php'),
            ], 'jev-config');
        }

        // Register macro on Rule if Illuminate\Validation\Rule exists
        if (class_exists(Rule::class)) {
            Rule::macro('jev', function () {
                return new JevRule();
            });
        }
    }
}
