<?php

declare(strict_types=1);

namespace Oziri\LlmSuite\Support;

/**
 * Represents a response from an image generation request.
 */
class ImageResponse
{
    public function __construct(
        public ?string $url = null,
        public ?string $base64 = null,
        public array $raw = [],
        public ?string $revisedPrompt = null,
    ) {}

    /**
     * Check if the response contains a URL.
     */
    public function hasUrl(): bool
    {
        return $this->url !== null && $this->url !== '';
    }

    /**
     * Check if the response contains base64 data.
     */
    public function hasBase64(): bool
    {
        return $this->base64 !== null && $this->base64 !== '';
    }

    /**
     * Get the image data (URL or base64).
     */
    public function getData(): ?string
    {
        return $this->url ?? $this->base64;
    }

    /**
     * Get the raw response data.
     */
    public function getRaw(): array
    {
        return $this->raw;
    }
}

