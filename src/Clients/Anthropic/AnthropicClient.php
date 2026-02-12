<?php

declare(strict_types=1);

namespace Oziri\LlmSuite\Clients\Anthropic;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Oziri\LlmSuite\Contracts\ChatClient;
use Oziri\LlmSuite\Exceptions\ProviderConfigException;
use Oziri\LlmSuite\Exceptions\ProviderRequestException;
use Oziri\LlmSuite\Support\ChatResponse;
use Oziri\LlmSuite\Support\TokenUsage;

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
     * API endpoint for listing models.
     */
    protected const ENDPOINT_MODELS = '/models';

    /**
     * Error message for failed chat requests.
     */
    protected const ERROR_CHAT_FAILED = 'Anthropic chat request failed';

    public function __construct(
        protected array $config
    ) {
        // Validate API key is required
        if (!isset($config['api_key']) || empty($config['api_key'])) {
            throw new ProviderConfigException(
                'API key is required for Anthropic provider'
            );
        }
    }

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

        $messages = $options['messages'] ?? [
            ['role' => 'user', 'content' => $prompt],
        ];

        $payload = [
            'model' => $options['model'] ?? $this->config['chat_model'] ?? self::DEFAULT_CHAT_MODEL,
            'messages' => $messages,
            'max_tokens' => $options['max_tokens'] ?? self::DEFAULT_MAX_TOKENS,
        ];

        if (isset($options['system'])) {
            $payload['system'] = $options['system'];
        }

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
        
        $content = '';
        if (isset($data['content']) && is_array($data['content'])) {
            foreach ($data['content'] as $block) {
                if (($block['type'] ?? '') === 'text') {
                    $content .= $block['text'] ?? '';
                }
            }
        }

        $tokenUsage = isset($data['usage']) 
            ? TokenUsage::fromArray($data['usage']) 
            : TokenUsage::empty();

        return new ChatResponse(
            content: $content,
            raw: $data,
            model: $data['model'] ?? null,
            id: $data['id'] ?? null,
            latencyMs: $latencyMs,
            tokenUsage: $tokenUsage,
        );
    }

    /**
     * Check if the Anthropic API is accessible.
     *
     * @return bool True if the API is accessible, false otherwise
     */
    public function isAvailable(): bool
    {
        try {
            $response = $this->http()->get(self::ENDPOINT_MODELS);
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get the list of available models from Anthropic.
     *
     * @return array<int, string> Array of model identifiers
     * @throws \Oziri\LlmSuite\Exceptions\ProviderRequestException
     */
    public function getAvailableModels(): array
    {
        try {
            $response = $this->http()->get(self::ENDPOINT_MODELS);

            if (! $response->successful()) {
                throw ProviderRequestException::fromResponse(
                    'Failed to fetch Anthropic models',
                    $response
                );
            }

            $data = $response->json();
            $models = is_array($data) ? $data : [];

            return array_map(
                fn($model) => $model['id'] ?? '',
                array_filter($models, fn($model) => !empty($model['id'] ?? ''))
            );
        } catch (ProviderRequestException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ProviderRequestException(
                'Error fetching Anthropic models: ' . $e->getMessage()
            );
        }
    }
}

