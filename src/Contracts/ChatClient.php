<?php

declare(strict_types=1);

namespace Oziri\LlmSuite\Contracts;

use Oziri\LlmSuite\Support\ChatResponse;

/**
 * Interface for LLM providers that support chat completions.
 */
interface ChatClient extends LlmClient
{
    /**
     * Send a chat message to the LLM provider.
     *
     * @param string $prompt The user's message/prompt
     * @param array $options Additional options (model, temperature, max_tokens, etc.)
     * @return ChatResponse
     */
    public function chat(string $prompt, array $options = []): ChatResponse;
}

