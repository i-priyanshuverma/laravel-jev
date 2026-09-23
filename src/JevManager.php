<?php

declare(strict_types=1);

namespace Priyanshu\LaravelJev;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Priyanshu\LaravelJev\Client\JevClient;
use Priyanshu\LaravelJev\Contracts\ClientInterface;
use Priyanshu\LaravelJev\Contracts\Jev as JevContract;
use Priyanshu\LaravelJev\Events\DecisionEvaluated;
use Priyanshu\LaravelJev\Support\BatchAnalysis;
use Priyanshu\LaravelJev\Support\BatchResult;
use Priyanshu\LaravelJev\Support\JevDecision;
use Priyanshu\LaravelJev\Support\Question;
use Priyanshu\LaravelJev\Testing\JevFake;

class JevManager implements JevContract
{
    protected ?ClientInterface $client = null;
    protected ?JevFake $fake = null;

    public function __construct(
        protected Application $app
    ) {}

    /**
     * Swap the manager with a testing fake.
     */
    public function fake(array $expectations = []): JevFake
    {
        $this->fake = new JevFake($expectations);

        $this->app->instance('jev', $this->fake);
        $this->app->instance(JevContract::class, $this->fake);

        return $this->fake;
    }

    /**
     * Determine if we are currently faking Jev.
     */
    public function isFaking(): bool
    {
        return $this->fake !== null;
    }

    /**
     * Boolean classification: Does the input meet the criteria?
     */
    public function is(string $input, string $criteria, ?float $threshold = null): bool
    {
        if ($this->fake) {
            return $this->fake->is($input, $criteria, $threshold ?? $this->defaultThreshold());
        }

        $decision = $this->evaluate($input, $criteria, $threshold);

        return $decision->passes($threshold ?? $this->defaultThreshold());
    }

    /**
     * Inverse boolean check.
     */
    public function isNot(string $input, string $criteria, ?float $threshold = null): bool
    {
        return ! $this->is($input, $criteria, $threshold);
    }

    /**
     * Categorical classification: Pick the single best option from the list.
     *
     * @param array<string, string>|list<string> $options
     */
    public function choose(string $input, array $options, ?string $default = null): string
    {
        if ($this->fake) {
            return $this->fake->choose($input, $options, $default);
        }

        $cacheKey = $this->cacheKey('choose', $input, json_encode($options));

        return (string) $this->remember($cacheKey, function () use ($input, $options) {
            $result = $this->getClient()->ask($input, [
                'selection' => Question::choice('Select the best matching option:', $options),
            ]);

            return $result->choice('selection')['choice'];
        });
    }

    /**
     * Rate input against ordered levels.
     *
     * @param list<string> $levels
     */
    public function score(string $input, string $criteria, array $levels = ['low', 'medium', 'high']): float
    {
        if ($this->fake) {
            return $this->fake->score($input, $criteria, $levels);
        }

        $cacheKey = $this->cacheKey('score', $input, $criteria . json_encode($levels));

        return (float) $this->remember($cacheKey, function () use ($input, $criteria, $levels) {
            $result = $this->getClient()->ask($input, [
                'rating' => Question::score("Rate {$criteria}:", $levels),
            ]);

            return $result->score('rating')['score'];
        });
    }

    /**
     * Full evaluation returning a rich JevDecision object.
     */
    public function evaluate(string $input, string $criteria, ?float $threshold = null): JevDecision
    {
        if ($this->fake) {
            return $this->fake->evaluate($input, $criteria, $threshold ?? $this->defaultThreshold());
        }

        $cacheKey = $this->cacheKey('eval', $input, $criteria);

        $decision = $this->remember($cacheKey, function () use ($input, $criteria) {
            $result = $this->getClient()->ask($input, [
                'noul' => Question::noul("Is this {$criteria}?"),
            ]);

            return JevDecision::fromAnswer($result->get('noul'), $result->latencyMs);
        });

        $this->dispatch(new DecisionEvaluated($input, $criteria, $decision, $decision->latencyMs()));

        return $decision;
    }

    /**
     * Fluent multi-question batching.
     */
    public function analyze(string $input): BatchAnalysis
    {
        return new BatchAnalysis($this, $input);
    }

    /**
     * Execute batch questions through client.
     */
    public function runBatch(string $state, array $questions): BatchResult
    {
        if ($this->fake) {
            return $this->fake->runBatch($state, $questions);
        }

        $result = $this->getClient()->ask($state, $questions);

        return new BatchResult($result, $this->defaultThreshold(), $result->latencyMs);
    }

    public function getClient(): ClientInterface
    {
        if ($this->client === null) {
            $apiKey = (string) $this->app['config']->get('jev.api_key', env('JEV_API_KEY', ''));
            $baseUrl = (string) $this->app['config']->get('jev.base_url', 'https://api.typesafe.ai/v1');
            $timeout = (int) $this->app['config']->get('jev.timeout', 5);
            $retries = (int) $this->app['config']->get('jev.retries', 2);

            $this->client = new JevClient(
                apiKey: $apiKey,
                baseUrl: $baseUrl,
                timeout: $timeout,
                retries: $retries
            );
        }

        return $this->client;
    }

    public function setClient(ClientInterface $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function defaultThreshold(): float
    {
        return (float) $this->app['config']->get('jev.threshold', 0.80);
    }

    protected function cacheKey(string $type, string $input, string $extra): string
    {
        return 'jev:' . md5("{$type}:{$input}:{$extra}");
    }

    protected function remember(string $key, Closure $callback): mixed
    {
        if (! $this->app['config']->get('jev.cache.enabled', false)) {
            return $callback();
        }

        $store = $this->app['config']->get('jev.cache.store');
        $ttl = (int) $this->app['config']->get('jev.cache.ttl', 3600);

        return Cache::store($store)->remember($key, $ttl, $callback);
    }

    protected function dispatch(object $event): void
    {
        if (isset($this->app['events'])) {
            $this->app['events']->dispatch($event);
        }
    }

    /**
     * Pass dynamic assertion calls to fake if active.
     */
    public function __call(string $method, array $parameters): mixed
    {
        if ($this->fake && method_exists($this->fake, $method)) {
            return $this->fake->{$method}(...$parameters);
        }

        throw new \BadMethodCallException("Method [{$method}] does not exist on " . static::class);
    }
}
