<?php

declare(strict_types=1);

namespace Oziri\LlmSuite\Exceptions;

/**
 * Exception thrown when provider configuration is missing or invalid.
 */
class ProviderConfigException extends LlmException
{
    /**
     * Create an exception for a missing provider configuration.
     */
    public static function missingProvider(string $name): static
    {
        return new static("LLM provider [{$name}] is not configured.");
    }

    /**
     * Create an exception for an unsupported driver.
     */
    public static function unsupportedDriver(string $driver): static
    {
        return new static("Unsupported LLM driver [{$driver}].");
    }

    /**
     * Create an exception for a missing API key.
     */
    public static function missingApiKey(string $provider): static
    {
        return new static("API key is not configured for provider [{$provider}].");
    }
}

