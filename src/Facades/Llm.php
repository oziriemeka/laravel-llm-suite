<?php

declare(strict_types=1);

namespace Oziri\LlmSuite\Facades;

use Illuminate\Support\Facades\Facade;
use Oziri\LlmSuite\Contracts\ChatClient;
use Oziri\LlmSuite\Contracts\ImageClient;
use Oziri\LlmSuite\Contracts\LlmClient;
use Oziri\LlmSuite\Helpers\LlmFake;
use Oziri\LlmSuite\Managers\LlmManager;
use Oziri\LlmSuite\Support\ChatResponse;
use Oziri\LlmSuite\Support\ImageResponse;

/**
 * Facade for the LLM Suite manager.
 *
 * @method static LlmManager using(string $name)
 * @method static string chat(string $prompt, array $options = [])
 * @method static ChatResponse chatWithResponse(string $prompt, array $options = [])
 * @method static ChatClient chatClient()
 * @method static ImageClient image()
 * @method static ImageResponse generateImage(array $params)
 * @method static LlmClient client(?string $name = null)
 * @method static string getDefaultProvider()
 * @method static array getProviders()
 * @method static LlmManager extend(string $driver, callable $callback)
 * @method static LlmManager forgetClient(string $name)
 * @method static LlmManager forgetAllClients()
 * @method static array getConfig()
 *
 * @see \Oziri\LlmSuite\Managers\LlmManager
 */
class Llm extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'llm-suite';
    }

    /**
     * Replace the bound instance with a fake for testing.
     */
    public static function fake(): LlmFake
    {
        return LlmFake::create();
    }
}

