<?php

declare(strict_types=1);

namespace Priyanshu\LaravelJev;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Priyanshu\LaravelJev\Client\JevClient;
use Priyanshu\LaravelJev\Support\BatchAnalysis;
use Priyanshu\LaravelJev\Support\BatchResult;
use Priyanshu\LaravelJev\Support\JevDecision;
use Priyanshu\LaravelJev\Support\Question;
use Priyanshu\LaravelJev\Testing\JevFake;

class JevManager
{
    protected ?JevClient $client = null;
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
        if ($cached = $this->getFromCache($cacheKey)) {
            return (string) $cached;
        }

        $result = $this->getClient()->ask($input, [
            'selection' => Question::choice('Select the best matching option:', $options),
        ]);

        $choice = $result->choice('selection')['choice'];

        $this->putInCache($cacheKey, $choice);

        return $choice;
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
        if ($cached = $this->getFromCache($cacheKey)) {
            return (float) $cached;
        }

        $result = $this->getClient()->ask($input, [
            'rating' => Question::score("Rate {$criteria}:", $levels),
        ]);

        $score = $result->score('rating')['score'];

        $this->putInCache($cacheKey, $score);

        return $score;
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
        if ($cached = $this->getFromCache($cacheKey)) {
            return $cached;
        }

        $result = $this->getClient()->ask($input, [
            'noul' => Question::noul("Is this {$criteria}?"),
        ]);

        $noul = $result->noul('noul');

        $decision = new JevDecision(
            value: $noul['isTrue'],
            confidence: $noul['confidence'],
            type: 'noul',
            probabilities: ['true' => $noul['probability'], 'false' => 1.0 - $noul['probability']],
            latencyMs: $result->latencyMs,
            raw: $result->get('noul')
        );

        $this->putInCache($cacheKey, $decision);

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

    public function getClient(): JevClient
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

    public function setClient(JevClient $client): self
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

    protected function getFromCache(string $key): mixed
    {
        if (! $this->app['config']->get('jev.cache.enabled', false)) {
            return null;
        }

        $store = $this->app['config']->get('jev.cache.store');

        return Cache::store($store)->get($key);
    }

    protected function putInCache(string $key, mixed $value): void
    {
        if (! $this->app['config']->get('jev.cache.enabled', false)) {
            return;
        }

        $store = $this->app['config']->get('jev.cache.store');
        $ttl = (int) $this->app['config']->get('jev.cache.ttl', 3600);

        Cache::store($store)->put($key, $value, $ttl);
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
