<?php

declare(strict_types=1);

namespace Oziri\LlmSuite\Tests\Unit;

use Oziri\LlmSuite\Clients\Dummy\DummyClient;
use Oziri\LlmSuite\Contracts\ChatClient;
use Oziri\LlmSuite\Contracts\ImageClient;
use Oziri\LlmSuite\Exceptions\ProviderConfigException;
use Oziri\LlmSuite\Managers\LlmManager;
use Oziri\LlmSuite\Tests\TestCase;

class LlmManagerTest extends TestCase
{
    protected function getManager(array $config = []): LlmManager
    {
        $defaultConfig = [
            'default' => 'dummy',
            'providers' => [
                'dummy' => [
                    'driver' => 'dummy',
                ],
            ],
        ];

        return new LlmManager(array_merge($defaultConfig, $config));
    }

    public function test_can_get_default_provider(): void
    {
        $manager = $this->getManager();

        $this->assertEquals('dummy', $manager->getDefaultProvider());
    }

    public function test_can_resolve_client(): void
    {
        $manager = $this->getManager();
        $client = $manager->client();

        $this->assertInstanceOf(DummyClient::class, $client);
    }

    public function test_client_implements_chat_interface(): void
    {
        $manager = $this->getManager();
        $client = $manager->client();

        $this->assertInstanceOf(ChatClient::class, $client);
    }

    public function test_client_implements_image_interface(): void
    {
        $manager = $this->getManager();
        $client = $manager->client();

        $this->assertInstanceOf(ImageClient::class, $client);
    }

    public function test_can_switch_provider_using_using_method(): void
    {
        $manager = $this->getManager([
            'providers' => [
                'dummy' => ['driver' => 'dummy'],
                'another' => ['driver' => 'dummy'],
            ],
        ]);

        $result = $manager->using('another');

        $this->assertSame($manager, $result);
    }

    public function test_throws_exception_for_missing_provider(): void
    {
        $manager = $this->getManager();

        $this->expectException(ProviderConfigException::class);
        $this->expectExceptionMessage('LLM provider [nonexistent] is not configured.');

        $manager->client('nonexistent');
    }

    public function test_throws_exception_for_unsupported_driver(): void
    {
        $manager = new LlmManager([
            'default' => 'test',
            'providers' => [
                'test' => ['driver' => 'unsupported'],
            ],
        ]);

        $this->expectException(ProviderConfigException::class);
        $this->expectExceptionMessage('Unsupported LLM driver [unsupported].');

        $manager->client();
    }

    public function test_can_send_chat_message(): void
    {
        $manager = $this->getManager();
        $response = $manager->chat('Hello');

        $this->assertIsString($response);
        $this->assertStringContainsString('Hello', $response);
    }

    public function test_can_generate_image(): void
    {
        $manager = $this->getManager();
        $response = $manager->image()->generate(['prompt' => 'A cat']);

        $this->assertNotNull($response->url);
    }

    public function test_can_extend_with_custom_driver(): void
    {
        $manager = $this->getManager([
            'providers' => [
                'custom' => ['driver' => 'custom'],
            ],
        ]);

        $customClient = new DummyClient(['chat_response' => 'Custom response']);

        $manager->extend('custom', function () use ($customClient) {
            return $customClient;
        });

        $result = $manager->using('custom')->chat('Test');

        $this->assertEquals('Custom response', $result);
    }

    public function test_clients_are_cached(): void
    {
        $manager = $this->getManager();

        $client1 = $manager->client('dummy');
        $client2 = $manager->client('dummy');

        $this->assertSame($client1, $client2);
    }

    public function test_can_forget_cached_client(): void
    {
        $manager = $this->getManager();

        $client1 = $manager->client('dummy');
        $manager->forgetClient('dummy');
        $client2 = $manager->client('dummy');

        $this->assertNotSame($client1, $client2);
    }

    public function test_can_list_providers(): void
    {
        $manager = $this->getManager([
            'providers' => [
                'dummy' => ['driver' => 'dummy'],
                'another' => ['driver' => 'dummy'],
            ],
        ]);

        $providers = $manager->getProviders();

        $this->assertContains('dummy', $providers);
        $this->assertContains('another', $providers);
    }
}

