<?php

declare(strict_types=1);

namespace Oziri\LlmSuite\Tests\Unit;

use Oziri\LlmSuite\Clients\Dummy\DummyClient;
use Oziri\LlmSuite\Support\ChatResponse;
use Oziri\LlmSuite\Support\ImageResponse;
use Oziri\LlmSuite\Tests\TestCase;

class DummyClientTest extends TestCase
{
    public function test_returns_default_chat_response(): void
    {
        $client = new DummyClient();
        $response = $client->chat('Hello');

        $this->assertInstanceOf(ChatResponse::class, $response);
        $this->assertStringContainsString('Hello', $response->content);
    }

    public function test_returns_custom_chat_response(): void
    {
        $client = new DummyClient(['chat_response' => 'Custom response']);
        $response = $client->chat('Hello');

        $this->assertEquals('Custom response', $response->content);
    }

    public function test_can_set_chat_response(): void
    {
        $client = new DummyClient();
        $client->setChatResponse('Modified response');
        $response = $client->chat('Hello');

        $this->assertEquals('Modified response', $response->content);
    }

    public function test_returns_default_image_response(): void
    {
        $client = new DummyClient();
        $response = $client->generate(['prompt' => 'A cat']);

        $this->assertInstanceOf(ImageResponse::class, $response);
        $this->assertEquals('https://example.com/dummy-image.png', $response->url);
    }

    public function test_returns_custom_image_url(): void
    {
        $client = new DummyClient(['image_url' => 'https://custom.com/image.png']);
        $response = $client->generate(['prompt' => 'A cat']);

        $this->assertEquals('https://custom.com/image.png', $response->url);
    }

    public function test_can_set_image_url(): void
    {
        $client = new DummyClient();
        $client->setImageUrl('https://modified.com/image.png');
        $response = $client->generate(['prompt' => 'A cat']);

        $this->assertEquals('https://modified.com/image.png', $response->url);
    }

    public function test_tracks_chat_history(): void
    {
        $client = new DummyClient();
        $client->chat('First message');
        $client->chat('Second message', ['model' => 'test']);

        $history = $client->getChatHistory();

        $this->assertCount(2, $history);
        $this->assertEquals('First message', $history[0]['prompt']);
        $this->assertEquals('Second message', $history[1]['prompt']);
        $this->assertEquals('test', $history[1]['options']['model']);
    }

    public function test_tracks_image_history(): void
    {
        $client = new DummyClient();
        $client->generate(['prompt' => 'A cat']);
        $client->generate(['prompt' => 'A dog', 'size' => '512x512']);

        $history = $client->getImageHistory();

        $this->assertCount(2, $history);
        $this->assertEquals('A cat', $history[0]['prompt']);
        $this->assertEquals('A dog', $history[1]['prompt']);
        $this->assertEquals('512x512', $history[1]['size']);
    }

    public function test_can_clear_history(): void
    {
        $client = new DummyClient();
        $client->chat('Hello');
        $client->generate(['prompt' => 'A cat']);

        $client->clearHistory();

        $this->assertEmpty($client->getChatHistory());
        $this->assertEmpty($client->getImageHistory());
    }
}

