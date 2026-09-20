<?php

declare(strict_types=1);

namespace Priyanshu\LaravelJev\Client;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Priyanshu\LaravelJev\Exceptions\JevApiException;
use Priyanshu\LaravelJev\Support\JevResult;
use Priyanshu\LaravelJev\Support\Question;

class JevClient
{
    public function __construct(
        protected string $apiKey,
        protected string $baseUrl = 'https://api.typesafe.ai/v1',
        protected int $timeout = 5,
        protected int $retries = 2
    ) {}

    /**
     * Send state and questions to TypeSafe Jev API.
     *
     * @param array<string, Question|array<string, mixed>> $questions
     * @throws JevApiException
     */
    public function ask(string $state, array $questions): JevResult
    {
        if (empty($this->apiKey)) {
            throw new JevApiException('TypeSafe Jev API key is missing. Set JEV_API_KEY in your .env file.');
        }

        $serializedQuestions = [];
        foreach ($questions as $name => $question) {
            if ($question instanceof Question) {
                $serializedQuestions[$name] = $question->toArray();
            } elseif (is_array($question)) {
                $serializedQuestions[$name] = $question;
            } else {
                throw new JevApiException("Question [{$name}] must be an instance of Question or an array.");
            }
        }

        $start = hrtime(true);

        try {
            $response = $this->buildRequest()->post("{$this->baseUrl}/decide", [
                'state' => $state,
                'questions' => $serializedQuestions,
            ]);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $status = $e->response ? $e->response->status() : 0;
            $body = $e->response ? ($e->response->json() ?? $e->response->body()) : null;
            throw new JevApiException("Jev API returned HTTP error [{$status}]: " . $e->getMessage(), $status, $body, $e);
        } catch (\Throwable $e) {
            throw new JevApiException("Failed to communicate with Jev API: {$e->getMessage()}", 0, null, $e instanceof \Exception ? $e : null);
        }

        $latency = (int) ((hrtime(true) - $start) / 1e6);

        if (! $response->successful()) {
            throw new JevApiException(
                message: "Jev API returned HTTP error [{$response->status()}]: " . $response->body(),
                statusCode: $response->status(),
                responseBody: $response->json() ?? $response->body()
            );
        }

        $json = $response->json();

        return JevResult::fromArray((array) $json, $latency);
    }

    protected function buildRequest(): PendingRequest
    {
        $req = Http::withToken($this->apiKey)
            ->acceptJson()
            ->asJson()
            ->timeout($this->timeout);

        if ($this->retries > 0) {
            $req->retry($this->retries, 100, throw: false);
        }

        return $req;
    }

    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = rtrim($baseUrl, '/');

        return $this;
    }

    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }
}
