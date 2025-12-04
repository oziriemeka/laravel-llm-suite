<?php

declare(strict_types=1);

namespace Oziri\LlmSuite\Managers;

use Illuminate\Support\Arr;
use Oziri\LlmSuite\Clients\Anthropic\AnthropicClient;
use Oziri\LlmSuite\Clients\Dummy\DummyClient;
use Oziri\LlmSuite\Clients\LmStudio\LmStudioClient;
use Oziri\LlmSuite\Clients\OpenAI\OpenAIClient;
use Oziri\LlmSuite\Contracts\ChatClient;
use Oziri\LlmSuite\Contracts\ImageClient;
use Oziri\LlmSuite\Contracts\LlmClient;
use Oziri\LlmSuite\Exceptions\ProviderConfigException;
use Oziri\LlmSuite\Support\ChatResponse;
use Oziri\LlmSuite\Support\ImageResponse;

/**
 * LLM Manager - handles driver resolution and provides a unified API.
 * Works like Laravel's Storage::disk() or Mail::mailer() pattern.
 */
class LlmManager
{
    /**
     * The configuration array.
     */
    protected array $config;

    /**
     * The resolved client instances.
     */
    protected array $clients = [];

    /**
     * The current provider being used.
     */
    protected ?string $current = null;

    /**
     * Custom driver creators.
     */
    protected array $customCreators = [];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Switch to a different provider for the next operation.
     */
    public function using(string $name): static
    {
        $this->current = $name;

        return $this;
    }

    /**
     * Get the name of the default provider.
     */
    public function getDefaultProvider(): string
    {
        return $this->config['default'] ?? 'openai';
    }

    /**
     * Send a chat message using the current provider.
     */
    public function chat(string $prompt, array $options = []): string
    {
        return $this->chatClient()->chat($prompt, $options)->content;
    }

    /**
     * Send a chat message and get the full response object.
     */
    public function chatWithResponse(string $prompt, array $options = []): ChatResponse
    {
        return $this->chatClient()->chat($prompt, $options);
    }

    /**
     * Get the chat client for the current provider.
     */
    public function chatClient(): ChatClient
    {
        $client = $this->client();

        if (! $client instanceof ChatClient) {
            $name = $this->getCurrentProviderName();
            throw new \InvalidArgumentException("LLM provider [{$name}] does not support chat.");
        }

        return $client;
    }

    /**
     * Get the image client for the current provider.
     */
    public function image(): ImageClient
    {
        $client = $this->client();

        if (! $client instanceof ImageClient) {
            $name = $this->getCurrentProviderName();
            throw new \InvalidArgumentException("LLM provider [{$name}] does not support image generation.");
        }

        return $client;
    }

    /**
     * Generate an image using the current provider.
     * Convenience method that wraps image()->generate().
     */
    public function generateImage(array $params): ImageResponse
    {
        return $this->image()->generate($params);
    }

    /**
     * Get the client for a specific provider or the current/default provider.
     */
    public function client(?string $name = null): LlmClient
    {
        $name = $name ?? $this->getCurrentProviderName();

        // Reset current after getting the name
        $this->current = null;

        if (! isset($this->clients[$name])) {
            $this->clients[$name] = $this->resolve($name);
        }

        return $this->clients[$name];
    }

    /**
     * Get the current provider name (from using() or default).
     */
    protected function getCurrentProviderName(): string
    {
        return $this->current ?? $this->getDefaultProvider();
    }

    /**
     * Resolve a provider by name.
     */
    protected function resolve(string $name): LlmClient
    {
        $config = Arr::get($this->config, "providers.{$name}");

        if (! $config) {
            throw ProviderConfigException::missingProvider($name);
        }

        $driver = $config['driver'] ?? null;

        // Check for custom creators first
        if (isset($this->customCreators[$driver])) {
            return $this->customCreators[$driver]($config);
        }

        return match ($driver) {
            'openai' => $this->createOpenAiClient($config),
            'anthropic' => $this->createAnthropicClient($config),
            'lmstudio' => $this->createLmStudioClient($config),
            'dummy' => $this->createDummyClient($config),
            default => throw ProviderConfigException::unsupportedDriver($driver ?? 'null'),
        };
    }

    /**
     * Create an OpenAI client instance.
     */
    protected function createOpenAiClient(array $config): OpenAIClient
    {
        return new OpenAIClient($config);
    }

    /**
     * Create an Anthropic client instance.
     */
    protected function createAnthropicClient(array $config): AnthropicClient
    {
        return new AnthropicClient($config);
    }

    /**
     * Create an LM Studio client instance.
     */
    protected function createLmStudioClient(array $config): LmStudioClient
    {
        return new LmStudioClient($config);
    }

    /**
     * Create a Dummy client instance.
     */
    protected function createDummyClient(array $config): DummyClient
    {
        return new DummyClient($config);
    }

    /**
     * Register a custom driver creator.
     */
    public function extend(string $driver, callable $callback): static
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

    /**
     * Get all registered provider names.
     */
    public function getProviders(): array
    {
        return array_keys($this->config['providers'] ?? []);
    }

    /**
     * Forget a cached client instance.
     */
    public function forgetClient(string $name): static
    {
        unset($this->clients[$name]);

        return $this;
    }

    /**
     * Forget all cached client instances.
     */
    public function forgetAllClients(): static
    {
        $this->clients = [];

        return $this;
    }

    /**
     * Get the full configuration array.
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}

