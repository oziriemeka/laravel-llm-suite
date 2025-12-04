<?php

declare(strict_types=1);

namespace Oziri\LlmSuite\Clients\Anthropic;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Oziri\LlmSuite\Contracts\ChatClient;
use Oziri\LlmSuite\Exceptions\ProviderRequestException;
use Oziri\LlmSuite\Support\ChatResponse;

/**
 * Anthropic (Claude) API client implementation.
 * Supports chat completions via the Messages API.
 */
class AnthropicClient implements ChatClient
{
    /**
     * Anthropic API version.
     */
    protected const API_VERSION = '2023-06-01';

    /**
     * Default base URL for Anthropic API.
     */
    protected const DEFAULT_BASE_URL = 'https://api.anthropic.com/v1';

    /**
     * Default chat model.
     */
    protected const DEFAULT_CHAT_MODEL = 'claude-3-5-sonnet-20241022';

    /**
     * Default max tokens for responses.
     */
    protected const DEFAULT_MAX_TOKENS = 4096;

    /**
     * API endpoint for messages (chat).
     */
    protected const ENDPOINT_MESSAGES = '/messages';

    /**
     * Error message for failed chat requests.
     */
    protected const ERROR_CHAT_FAILED = 'Anthropic chat request failed';

    public function __construct(
        protected array $config
    ) {}

    /**
     * Get a configured HTTP client for Anthropic API requests.
     */
    protected function http(): PendingRequest
    {
        return Http::withHeaders([
            'x-api-key' => $this->config['api_key'],
            'anthropic-version' => self::API_VERSION,
        ])
            ->baseUrl($this->config['base_url'] ?? self::DEFAULT_BASE_URL)
            ->acceptJson()
            ->asJson();
    }

    /**
     * Send a chat message to Anthropic Claude.
     */
    public function chat(string $prompt, array $options = []): ChatResponse
    {
        $startTime = microtime(true);

        // Anthropic uses a different message format
        $messages = $options['messages'] ?? [
            ['role' => 'user', 'content' => $prompt],
        ];

        $payload = [
            'model' => $options['model'] ?? $this->config['chat_model'] ?? self::DEFAULT_CHAT_MODEL,
            'messages' => $messages,
            'max_tokens' => $options['max_tokens'] ?? self::DEFAULT_MAX_TOKENS,
        ];

        // Anthropic handles system prompts separately
        if (isset($options['system'])) {
            $payload['system'] = $options['system'];
        }

        // Add optional parameters if provided
        if (isset($options['temperature'])) {
            $payload['temperature'] = $options['temperature'];
        }

        if (isset($options['top_p'])) {
            $payload['top_p'] = $options['top_p'];
        }

        if (isset($options['top_k'])) {
            $payload['top_k'] = $options['top_k'];
        }

        $response = $this->http()->post(self::ENDPOINT_MESSAGES, $payload);

        if (! $response->successful()) {
            throw ProviderRequestException::fromResponse(self::ERROR_CHAT_FAILED, $response);
        }

        $latencyMs = (microtime(true) - $startTime) * 1000;

        $data = $response->json();
        
        // Anthropic returns content as an array of content blocks
        $content = '';
        if (isset($data['content']) && is_array($data['content'])) {
            foreach ($data['content'] as $block) {
                if (($block['type'] ?? '') === 'text') {
                    $content .= $block['text'] ?? '';
                }
            }
        }

        return new ChatResponse(
            content: $content,
            raw: $data,
            model: $data['model'] ?? null,
            id: $data['id'] ?? null,
            latencyMs: $latencyMs,
        );
    }
}

