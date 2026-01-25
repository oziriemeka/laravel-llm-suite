<?php

declare(strict_types=1);

namespace Oziri\LlmSuite\Tests\Unit;

use Illuminate\Support\Facades\Http;
use Oziri\LlmSuite\Clients\Anthropic\AnthropicClient;
use Oziri\LlmSuite\Exceptions\ProviderConfigException;
use Oziri\LlmSuite\Exceptions\ProviderRequestException;
use Oziri\LlmSuite\Tests\TestCase;

class AnthropicClientTest extends TestCase
{
    private AnthropicClient $client;
    private array $config;

    protected function setUp(): void
    {
        parent::setUp();

        // Use config values if available, otherwise use safe test defaults
        $anthropicConfig = config('llm-suite.providers.anthropic', []);

        $this->config = [
            'api_key' => $anthropicConfig['api_key'] ?? 'test-api-key-for-testing-only',
            'base_url' => $anthropicConfig['base_url'] ?? 'https://api.anthropic.com/v1',
            'chat_model' => $anthropicConfig['chat_model'] ?? 'claude-3-5-sonnet-20241022',
        ];

        $this->client = new AnthropicClient($this->config);
    }

    protected function tearDown(): void
    {
        Http::assertNothingSent();
        parent::tearDown();
    }

    /**
     * Test constructor throws exception when API key is missing.
     */
    public function test_constructor_throws_exception_without_api_key(): void
    {
        $this->expectException(ProviderConfigException::class);
        $this->expectExceptionMessage('API key is required for Anthropic provider');

        new AnthropicClient([
            'base_url' => 'https://api.anthropic.com/v1',
            'chat_model' => 'claude-3-5-sonnet-20241022',
        ]);
    }

    /**
     * Test constructor throws exception when API key is empty.
     */
    public function test_constructor_throws_exception_with_empty_api_key(): void
    {
        $this->expectException(ProviderConfigException::class);
        $this->expectExceptionMessage('API key is required for Anthropic provider');

        new AnthropicClient([
            'api_key' => '',
            'base_url' => 'https://api.anthropic.com/v1',
            'chat_model' => 'claude-3-5-sonnet-20241022',
        ]);
    }

    /**
     * Test constructor accepts valid configuration with API key.
     */
    public function test_constructor_accepts_valid_configuration(): void
    {
        $client = new AnthropicClient([
            'api_key' => 'test-api-key',
            'base_url' => 'https://api.anthropic.com/v1',
            'chat_model' => 'claude-3-5-sonnet-20241022',
        ]);

        $this->assertInstanceOf(AnthropicClient::class, $client);
    }

    /**
     * Test successful model retrieval.
     */
    public function test_get_available_models_returns_array_of_models(): void
    {
        $baseUrl = $this->config['base_url'];
        Http::fake([
            "{$baseUrl}/models" => Http::response([
                [
                    'id' => 'claude-3-5-sonnet-20241022',
                    'type' => 'model',
                    'display_name' => 'Claude 3.5 Sonnet',
                    'created_at' => '2024-10-22T00:00:00Z',
                ],
                [
                    'id' => 'claude-3-opus-20240229',
                    'type' => 'model',
                    'display_name' => 'Claude 3 Opus',
                    'created_at' => '2024-02-29T00:00:00Z',
                ],
                [
                    'id' => 'claude-3-sonnet-20240229',
                    'type' => 'model',
                    'display_name' => 'Claude 3 Sonnet',
                    'created_at' => '2024-02-29T00:00:00Z',
                ],
            ], 200),
        ]);

        $models = $this->client->getAvailableModels();

        $this->assertIsArray($models);
        $this->assertCount(3, $models);
        $this->assertContains('claude-3-5-sonnet-20241022', $models);
        $this->assertContains('claude-3-opus-20240229', $models);
        $this->assertContains('claude-3-sonnet-20240229', $models);
    }

    /**
     * Test handling of empty response.
     */
    public function test_get_available_models_handles_empty_response(): void
    {
        $baseUrl = $this->config['base_url'];
        Http::fake([
            "{$baseUrl}/models" => Http::response([], 200),
        ]);

        $models = $this->client->getAvailableModels();

        $this->assertIsArray($models);
        $this->assertEmpty($models);
    }

    /**
     * Test HTTP error handling.
     */
    public function test_get_available_models_throws_exception_on_http_error(): void
    {
        $baseUrl = $this->config['base_url'];
        Http::fake([
            "{$baseUrl}/models" => Http::response([
                'error' => [
                    'type' => 'authentication_error',
                    'message' => 'Invalid API key',
                ],
            ], 401),
        ]);

        $this->expectException(ProviderRequestException::class);
        $this->expectExceptionMessage('Failed to fetch Anthropic models');

        $this->client->getAvailableModels();
    }

    /**
     * Test network exception handling.
     */
    public function test_get_available_models_throws_exception_on_network_error(): void
    {
        $baseUrl = $this->config['base_url'];
        Http::fake([
            "{$baseUrl}/models" => function () {
                throw new \Exception('Network error');
            },
        ]);

        $this->expectException(ProviderRequestException::class);
        $this->expectExceptionMessage('Error fetching Anthropic models');

        $this->client->getAvailableModels();
    }

    /**
     * Test that correct headers are sent.
     */
    public function test_get_available_models_sends_correct_headers(): void
    {
        $baseUrl = $this->config['base_url'];
        Http::fake([
            "{$baseUrl}/models" => Http::response([], 200),
        ]);

        $this->client->getAvailableModels();

        Http::assertSent(function ($request) {
            $expectedApiKey = $this->config['api_key'];
            $expectedBaseUrl = $this->config['base_url'];
            
            return $request->hasHeader('x-api-key', $expectedApiKey) &&
                   $request->hasHeader('anthropic-version', '2023-06-01') &&
                   $request->url() === "{$expectedBaseUrl}/models";
        });
    }

    /**
     * Test filtering of invalid model entries.
     */
    public function test_get_available_models_filters_invalid_entries(): void
    {
        $baseUrl = $this->config['base_url'];
        Http::fake([
            "{$baseUrl}/models" => Http::response([
                [
                    'id' => 'claude-3-5-sonnet-20241022',
                    'type' => 'model',
                ],
                [
                    'type' => 'model',
                    // Missing 'id'
                ],
                [
                    'id' => '',
                    'type' => 'model',
                    // Empty 'id'
                ],
                [
                    'id' => 'claude-3-opus-20240229',
                    'type' => 'model',
                ],
            ], 200),
        ]);

        $models = $this->client->getAvailableModels();

        $this->assertCount(2, $models);
        $this->assertContains('claude-3-5-sonnet-20241022', $models);
        $this->assertContains('claude-3-opus-20240229', $models);
        $this->assertNotContains('', $models);
    }

    /**
     * Test isAvailable returns true when API is accessible.
     */
    public function test_is_available_returns_true_when_api_accessible(): void
    {
        $baseUrl = $this->config['base_url'];
        Http::fake([
            "{$baseUrl}/models" => Http::response([], 200),
        ]);

        $this->assertTrue($this->client->isAvailable());
    }

    /**
     * Test isAvailable returns false on error.
     */
    public function test_is_available_returns_false_on_error(): void
    {
        $baseUrl = $this->config['base_url'];
        Http::fake([
            "{$baseUrl}/models" => Http::response([], 500),
        ]);

        $this->assertFalse($this->client->isAvailable());
    }

    /**
     * Test isAvailable returns false on network exception.
     */
    public function test_is_available_returns_false_on_network_exception(): void
    {
        $baseUrl = $this->config['base_url'];
        Http::fake([
            "{$baseUrl}/models" => function () {
                throw new \Exception('Connection timeout');
            },
        ]);

        $this->assertFalse($this->client->isAvailable());
    }
}
