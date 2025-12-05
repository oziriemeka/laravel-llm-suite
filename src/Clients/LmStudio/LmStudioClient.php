<?php

declare(strict_types=1);

namespace Oziri\LlmSuite\Clients\LmStudio;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Oziri\LlmSuite\Contracts\ChatClient;
use Oziri\LlmSuite\Exceptions\ProviderRequestException;
use Oziri\LlmSuite\Support\ChatResponse;
use Oziri\LlmSuite\Support\TokenUsage;

/**
 * LM Studio client implementation.
 * LM Studio provides an OpenAI-compatible local API for running LLMs locally.
 * 
 * @see https://lmstudio.ai/
 */
class LmStudioClient implements ChatClient
{
    /**
     * Default protocol for LM Studio server.
     */
    protected const DEFAULT_PROTOCOL = 'http';

    /**
     * Default host for LM Studio server.
     */
    protected const DEFAULT_HOST = '127.0.0.1';

    /**
     * Default port for LM Studio server.
     */
    protected const DEFAULT_PORT = 1234;

    /**
     * Default timeout in seconds.
     */
    protected const DEFAULT_TIMEOUT = 120;

    /**
     * Default chat model name.
     */
    protected const DEFAULT_CHAT_MODEL = 'local-model';

    /**
     * API endpoint for chat completions.
     */
    protected const ENDPOINT_CHAT = '/chat/completions';

    /**
     * API endpoint for listing models.
     */
    protected const ENDPOINT_MODELS = '/models';

    /**
     * Error message for failed chat requests.
     */
    protected const ERROR_CHAT_FAILED = 'LM Studio chat request failed';

    public function __construct(
        protected array $config
    ) {}

    /**
     * Get the base URL for the LM Studio API.
     */
    protected function getBaseUrl(): string
    {
        // Allow full base_url override for maximum flexibility
        if (! empty($this->config['base_url'])) {
            return rtrim($this->config['base_url'], '/');
        }

        $protocol = $this->config['protocol'] ?? self::DEFAULT_PROTOCOL;
        $host = $this->config['host'] ?? self::DEFAULT_HOST;
        $port = $this->config['port'] ?? self::DEFAULT_PORT;

        return "{$protocol}://{$host}:{$port}/v1";
    }

    /**
     * Get a configured HTTP client for LM Studio API requests.
     */
    protected function http(): PendingRequest
    {
        $request = Http::baseUrl($this->getBaseUrl())
            ->acceptJson()
            ->asJson()
            ->timeout($this->config['timeout'] ?? self::DEFAULT_TIMEOUT);

        // LM Studio doesn't require auth, but accepts it if provided
        if (! empty($this->config['api_key'])) {
            $request->withToken($this->config['api_key']);
        }

        return $request;
    }

    /**
     * Send a chat message to LM Studio.
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
            'model' => $options['model'] ?? $this->config['chat_model'] ?? self::DEFAULT_CHAT_MODEL,
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

        // LM Studio specific: stop sequences
        if (isset($options['stop'])) {
            $payload['stop'] = $options['stop'];
        }

        $response = $this->http()->post(self::ENDPOINT_CHAT, $payload);

        if (! $response->successful()) {
            throw ProviderRequestException::fromResponse(self::ERROR_CHAT_FAILED, $response);
        }

        $latencyMs = (microtime(true) - $startTime) * 1000;

        $data = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? '';

        // Parse token usage from response (OpenAI-compatible format)
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
     * Check if LM Studio server is running and accessible.
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
     * Get the list of available models from LM Studio.
     */
    public function getAvailableModels(): array
    {
        try {
            $response = $this->http()->get(self::ENDPOINT_MODELS);
            
            if (! $response->successful()) {
                return [];
            }

            $data = $response->json();
            return array_column($data['data'] ?? [], 'id');
        } catch (\Exception $e) {
            return [];
        }
    }
}

