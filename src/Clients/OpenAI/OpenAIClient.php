<?php

declare(strict_types=1);

namespace Oziri\LlmSuite\Clients\OpenAI;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Oziri\LlmSuite\Contracts\ChatClient;
use Oziri\LlmSuite\Contracts\ImageClient;
use Oziri\LlmSuite\Exceptions\ProviderRequestException;
use Oziri\LlmSuite\Support\ChatResponse;
use Oziri\LlmSuite\Support\ImageResponse;

/**
 * OpenAI API client implementation.
 * Supports both chat completions and image generation.
 */
class OpenAIClient implements ChatClient, ImageClient
{
    public function __construct(
        protected array $config
    ) {}

    /**
     * Get a configured HTTP client for OpenAI API requests.
     */
    protected function http(): PendingRequest
    {
        return Http::withToken($this->config['api_key'])
            ->baseUrl($this->config['base_url'] ?? 'https://api.openai.com/v1')
            ->acceptJson()
            ->asJson();
    }

    /**
     * Send a chat message to OpenAI.
     */
    public function chat(string $prompt, array $options = []): ChatResponse
    {
        $startTime = microtime(true);

        $messages = $options['messages'] ?? [
            ['role' => 'user', 'content' => $prompt],
        ];

        // If a system prompt is provided, prepend it
        if (isset($options['system'])) {
            array_unshift($messages, ['role' => 'system', 'content' => $options['system']]);
        }

        $payload = [
            'model' => $options['model'] ?? $this->config['chat_model'] ?? 'gpt-4.1-mini',
            'messages' => $messages,
        ];

        // Add optional parameters if provided
        if (isset($options['temperature'])) {
            $payload['temperature'] = $options['temperature'];
        }

        if (isset($options['max_tokens'])) {
            $payload['max_tokens'] = $options['max_tokens'];
        }

        if (isset($options['top_p'])) {
            $payload['top_p'] = $options['top_p'];
        }

        $response = $this->http()->post('/chat/completions', $payload);

        if (! $response->successful()) {
            throw ProviderRequestException::fromResponse('OpenAI chat request failed', $response);
        }

        $latencyMs = (microtime(true) - $startTime) * 1000;

        $data = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? '';

        return new ChatResponse(
            content: $content,
            raw: $data,
            model: $data['model'] ?? null,
            id: $data['id'] ?? null,
            latencyMs: $latencyMs,
        );
    }

    /**
     * Generate an image using OpenAI's DALL-E.
     */
    public function generate(array $params): ImageResponse
    {
        $payload = [
            'model' => $params['model'] ?? $this->config['image_model'] ?? 'dall-e-3',
            'prompt' => $params['prompt'] ?? '',
            'size' => $params['size'] ?? '1024x1024',
            'n' => $params['n'] ?? 1,
        ];

        // Add optional parameters
        if (isset($params['quality'])) {
            $payload['quality'] = $params['quality'];
        }

        if (isset($params['style'])) {
            $payload['style'] = $params['style'];
        }

        if (isset($params['response_format'])) {
            $payload['response_format'] = $params['response_format'];
        }

        $response = $this->http()->post('/images/generations', $payload);

        if (! $response->successful()) {
            throw ProviderRequestException::fromResponse('OpenAI image request failed', $response);
        }

        $data = $response->json();
        $imageData = $data['data'][0] ?? [];

        return new ImageResponse(
            url: $imageData['url'] ?? null,
            base64: $imageData['b64_json'] ?? null,
            raw: $data,
            revisedPrompt: $imageData['revised_prompt'] ?? null,
        );
    }
}

