<?php

declare(strict_types=1);

namespace Oziri\LlmSuite\Support;

/**
 * Represents a single message in a chat conversation.
 */
class ChatMessage
{
    public const ROLE_SYSTEM = 'system';
    public const ROLE_USER = 'user';
    public const ROLE_ASSISTANT = 'assistant';

    public function __construct(
        public string $role,
        public string $content,
    ) {}

    /**
     * Create a system message.
     */
    public static function system(string $content): static
    {
        return new static(self::ROLE_SYSTEM, $content);
    }

    /**
     * Create a user message.
     */
    public static function user(string $content): static
    {
        return new static(self::ROLE_USER, $content);
    }

    /**
     * Create an assistant message.
     */
    public static function assistant(string $content): static
    {
        return new static(self::ROLE_ASSISTANT, $content);
    }

    /**
     * Convert the message to an array format suitable for API requests.
     */
    public function toArray(): array
    {
        return [
            'role' => $this->role,
            'content' => $this->content,
        ];
    }
}

