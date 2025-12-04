<?php

declare(strict_types=1);

namespace Oziri\LlmSuite\Contracts;

use Oziri\LlmSuite\Support\ImageResponse;

/**
 * Interface for LLM providers that support image generation.
 */
interface ImageClient extends LlmClient
{
    /**
     * Generate an image from a prompt.
     *
     * @param array $params Image generation parameters (prompt, size, model, etc.)
     * @return ImageResponse
     */
    public function generate(array $params): ImageResponse;
}

