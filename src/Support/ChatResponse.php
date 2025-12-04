<?php

declare(strict_types=1);

namespace Oziri\LlmSuite\Support;

/**
 * Represents a response from a chat completion request.
 */
class ChatResponse
{
    public function __construct(
        public string $content,
        public array $raw = [],
        public ?string $model = null,
        public ?string $id = null,
        public ?float $latencyMs = null,
    ) {}

    /**
     * Get the response content as a string.
     */
    public function __toString(): string
    {
        return $this->content;
    }

    /**
     * Get the raw response data.
     */
    public function getRaw(): array
    {
        return $this->raw;
    }

    /**
     * Check if the response is empty.
     */
    public function isEmpty(): bool
    {
        return empty($this->content);
    }
}

