<?php

declare(strict_types=1);

namespace Oziri\LlmSuite\Clients\Dummy;

use Oziri\LlmSuite\Contracts\ChatClient;
use Oziri\LlmSuite\Contracts\ImageClient;
use Oziri\LlmSuite\Support\ChatResponse;
use Oziri\LlmSuite\Support\ImageResponse;

/**
 * Dummy client for testing and offline development.
 * Returns configurable or default responses without making API calls.
 */
class DummyClient implements ChatClient, ImageClient
{
    /**
     * Default model name for dummy responses.
     */
    protected const DEFAULT_MODEL = 'dummy-model';

    /**
     * Default ID prefix for dummy responses.
     */
    protected const DEFAULT_ID_PREFIX = 'dummy-';

    /**
     * Default image URL for dummy responses.
     */
    protected const DEFAULT_IMAGE_URL = 'https://example.com/dummy-image.png';

    protected ?string $chatResponse = null;
    protected ?string $imageUrl = null;
    protected array $chatHistory = [];
    protected array $imageHistory = [];

    public function __construct(
        protected array $config = []
    ) {
        $this->chatResponse = $config['chat_response'] ?? null;
        $this->imageUrl = $config['image_url'] ?? null;
    }

    /**
     * Set a custom chat response for testing.
     */
    public function setChatResponse(string $response): static
    {
        $this->chatResponse = $response;

        return $this;
    }

    /**
     * Set a custom image URL for testing.
     */
    public function setImageUrl(string $url): static
    {
        $this->imageUrl = $url;

        return $this;
    }

    /**
     * Get the chat request history.
     */
    public function getChatHistory(): array
    {
        return $this->chatHistory;
    }

    /**
     * Get the image request history.
     */
    public function getImageHistory(): array
    {
        return $this->imageHistory;
    }

    /**
     * Clear all request history.
     */
    public function clearHistory(): static
    {
        $this->chatHistory = [];
        $this->imageHistory = [];

        return $this;
    }

    /**
     * Return a dummy chat response.
     */
    public function chat(string $prompt, array $options = []): ChatResponse
    {
        $this->chatHistory[] = [
            'prompt' => $prompt,
            'options' => $options,
        ];

        $content = $this->chatResponse ?? "This is a dummy response to: {$prompt}";

        return new ChatResponse(
            content: $content,
            raw: [
                'dummy' => true,
                'prompt' => $prompt,
                'options' => $options,
            ],
            model: self::DEFAULT_MODEL,
            id: self::DEFAULT_ID_PREFIX . uniqid(),
            latencyMs: 0.0,
        );
    }

    /**
     * Return a dummy image response.
     */
    public function generate(array $params): ImageResponse
    {
        $this->imageHistory[] = $params;

        $url = $this->imageUrl ?? self::DEFAULT_IMAGE_URL;

        return new ImageResponse(
            url: $url,
            base64: null,
            raw: [
                'dummy' => true,
                'params' => $params,
            ],
            revisedPrompt: $params['prompt'] ?? null,
        );
    }
}

