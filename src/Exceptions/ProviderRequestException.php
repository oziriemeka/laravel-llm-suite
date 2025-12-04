<?php

declare(strict_types=1);

namespace Oziri\LlmSuite\Exceptions;

use Illuminate\Http\Client\Response;

/**
 * Exception thrown when an API request to a provider fails.
 */
class ProviderRequestException extends LlmException
{
    protected ?Response $response;

    public function __construct(string $message, ?Response $response = null, int $code = 0)
    {
        parent::__construct($message, $code);
        $this->response = $response;
    }

    /**
     * Get the HTTP response that caused the exception.
     */
    public function getResponse(): ?Response
    {
        return $this->response;
    }

    /**
     * Get the response body as an array.
     */
    public function getResponseBody(): ?array
    {
        return $this->response?->json();
    }

    /**
     * Get the HTTP status code.
     */
    public function getStatusCode(): ?int
    {
        return $this->response?->status();
    }

    /**
     * Create an exception from an HTTP response.
     */
    public static function fromResponse(string $message, Response $response): static
    {
        $body = $response->json();
        $errorMessage = $body['error']['message'] ?? $message;
        
        return new static(
            message: "{$message}: {$errorMessage}",
            response: $response,
            code: $response->status()
        );
    }
}

