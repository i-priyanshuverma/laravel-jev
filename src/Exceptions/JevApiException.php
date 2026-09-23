<?php

declare(strict_types=1);

namespace Priyanshu\LaravelJev\Exceptions;

use Exception;

class JevApiException extends Exception implements JevException
{
    public function __construct(
        string $message,
        protected int $statusCode = 0,
        protected mixed $responseBody = null,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getResponseBody(): mixed
    {
        return $this->responseBody;
    }
}
