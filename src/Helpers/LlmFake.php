<?php

declare(strict_types=1);

namespace Oziri\LlmSuite\Helpers;

use Illuminate\Support\Facades\App;
use Oziri\LlmSuite\Clients\Dummy\DummyClient;
use Oziri\LlmSuite\Managers\LlmManager;

/**
 * Testing helper for faking LLM responses.
 * Provides a fluent interface for setting up fake responses in tests.
 */
class LlmFake
{
    protected DummyClient $client;
    protected LlmManager $manager;

    public function __construct()
    {
        $this->client = new DummyClient();
        $this->createFakeManager();
    }

    /**
     * Create a fake LLM manager that uses our custom dummy client.
     */
    protected function createFakeManager(): void
    {
        $config = [
            'default' => 'fake',
            'providers' => [
                'fake' => [
                    'driver' => 'fake',
                ],
            ],
        ];

        $this->manager = new LlmManager($config);

        // Register our custom fake driver that returns our client instance
        $client = $this->client;
        $this->manager->extend('fake', function () use ($client) {
            return $client;
        });

        // Override the manager binding in the container
        App::instance(LlmManager::class, $this->manager);
        App::instance('llm-suite', $this->manager);
    }

    /**
     * Set the chat response that should be returned.
     */
    public function shouldReturnChat(string $response): static
    {
        $this->client->setChatResponse($response);

        return $this;
    }

    /**
     * Set the image URL that should be returned.
     */
    public function shouldReturnImage(string $url): static
    {
        $this->client->setImageUrl($url);

        return $this;
    }

    /**
     * Get the chat request history.
     */
    public function getChatHistory(): array
    {
        return $this->client->getChatHistory();
    }

    /**
     * Get the image request history.
     */
    public function getImageHistory(): array
    {
        return $this->client->getImageHistory();
    }

    /**
     * Assert that a chat request was made with the given prompt.
     */
    public function assertChatSent(string $prompt): static
    {
        $history = $this->getChatHistory();
        $found = false;

        foreach ($history as $request) {
            if ($request['prompt'] === $prompt) {
                $found = true;
                break;
            }
        }

        if (! $found) {
            throw new \PHPUnit\Framework\AssertionFailedError(
                "Expected chat request with prompt [{$prompt}] was not sent."
            );
        }

        return $this;
    }

    /**
     * Assert that an image request was made with the given prompt.
     */
    public function assertImageSent(string $prompt): static
    {
        $history = $this->getImageHistory();
        $found = false;

        foreach ($history as $request) {
            if (($request['prompt'] ?? '') === $prompt) {
                $found = true;
                break;
            }
        }

        if (! $found) {
            throw new \PHPUnit\Framework\AssertionFailedError(
                "Expected image request with prompt [{$prompt}] was not sent."
            );
        }

        return $this;
    }

    /**
     * Assert that no chat requests were made.
     */
    public function assertNoChatSent(): static
    {
        $history = $this->getChatHistory();

        if (count($history) > 0) {
            throw new \PHPUnit\Framework\AssertionFailedError(
                'Expected no chat requests to be sent, but ' . count($history) . ' were sent.'
            );
        }

        return $this;
    }

    /**
     * Assert that no image requests were made.
     */
    public function assertNoImageSent(): static
    {
        $history = $this->getImageHistory();

        if (count($history) > 0) {
            throw new \PHPUnit\Framework\AssertionFailedError(
                'Expected no image requests to be sent, but ' . count($history) . ' were sent.'
            );
        }

        return $this;
    }

    /**
     * Assert a specific number of chat requests were made.
     */
    public function assertChatCount(int $count): static
    {
        $actual = count($this->getChatHistory());

        if ($actual !== $count) {
            throw new \PHPUnit\Framework\AssertionFailedError(
                "Expected {$count} chat requests, but {$actual} were sent."
            );
        }

        return $this;
    }

    /**
     * Assert a specific number of image requests were made.
     */
    public function assertImageCount(int $count): static
    {
        $actual = count($this->getImageHistory());

        if ($actual !== $count) {
            throw new \PHPUnit\Framework\AssertionFailedError(
                "Expected {$count} image requests, but {$actual} were sent."
            );
        }

        return $this;
    }

    /**
     * Clear all request history.
     */
    public function clearHistory(): static
    {
        $this->client->clearHistory();

        return $this;
    }

    /**
     * Get the underlying dummy client.
     */
    public function getClient(): DummyClient
    {
        return $this->client;
    }

    /**
     * Get the fake manager.
     */
    public function getManager(): LlmManager
    {
        return $this->manager;
    }

    /**
     * Static factory method for cleaner usage.
     */
    public static function create(): static
    {
        return new static();
    }
}
