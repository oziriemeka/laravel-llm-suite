# Laravel LLM Suite

A unified, driver-based Laravel toolkit for working with multiple LLM providers for chat, image generation, and more.

## Features

- **Unified API** - Same interface regardless of provider (OpenAI, Anthropic, LM Studio, etc.)
- **Driver Pattern** - Switch providers like Laravel's Storage or Mail systems
- **Local LLM Support** - Run models locally with LM Studio for development and testing
- **Laravel Native** - Config files, facades, service providers
- **Testable** - Built-in faking support for testing without API calls

## Supported Providers

| Provider | Driver | Chat | Image | Models List |
|----------|--------|:----:|:-----:|:-----------:|
| **OpenAI** | `openai` | Yes | Yes | Yes |
| **Anthropic** | `anthropic` | Yes | - | - |
| **LM Studio** | `lmstudio` | Yes | - | Yes |
| **Dummy** | `dummy` | Yes | Yes | - |

- **OpenAI** - GPT-4, GPT-4.1, DALL-E 3, and other OpenAI models
- **Anthropic** - Claude 3.5 Sonnet, Claude 3 Opus, and other Claude models
- **LM Studio** - Run any open-source LLM locally (Llama, Mistral, Phi, etc.)
- **Dummy** - For testing and offline development (returns configurable mock responses)

## Requirements

- PHP 8.1+
- Laravel 10.x, 11.x, or 12.x

## Installation

Install via Composer:

```bash
composer require oziri/laravel-llm-suite
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=llm-suite-config
```

## Configuration

Add your API keys to your `.env` file:

```env
# Default provider
LLM_SUITE_DEFAULT=openai

# OpenAI
OPENAI_API_KEY=your-openai-api-key
OPENAI_CHAT_MODEL=gpt-4.1-mini
OPENAI_IMAGE_MODEL=dall-e-3

# Anthropic
ANTHROPIC_API_KEY=your-anthropic-api-key
ANTHROPIC_CHAT_MODEL=claude-3-5-sonnet-20241022

# LM Studio (local)
LMSTUDIO_HOST=127.0.0.1
LMSTUDIO_PORT=1234
LMSTUDIO_API_KEY=        # Optional - leave empty if not using authentication
LMSTUDIO_TIMEOUT=120
```

The configuration file (`config/llm-suite.php`) allows you to customize providers:

```php
return [
    'default' => env('LLM_SUITE_DEFAULT', 'openai'),

    'providers' => [
        'openai' => [
            'driver' => 'openai',
            'api_key' => env('OPENAI_API_KEY'),
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            'chat_model' => env('OPENAI_CHAT_MODEL', 'gpt-4.1-mini'),
            'image_model' => env('OPENAI_IMAGE_MODEL', 'dall-e-3'),
        ],

        'anthropic' => [
            'driver' => 'anthropic',
            'api_key' => env('ANTHROPIC_API_KEY'),
            'base_url' => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com/v1'),
            'chat_model' => env('ANTHROPIC_CHAT_MODEL', 'claude-3-5-sonnet-20241022'),
        ],

        'lmstudio' => [
            'driver' => 'lmstudio',
            'host' => env('LMSTUDIO_HOST', '127.0.0.1'),
            'port' => env('LMSTUDIO_PORT', 1234),
            'api_key' => env('LMSTUDIO_API_KEY'),
            'chat_model' => env('LMSTUDIO_CHAT_MODEL', 'local-model'),
            'timeout' => env('LMSTUDIO_TIMEOUT', 120),
        ],

        'dummy' => [
            'driver' => 'dummy',
        ],
    ],
];
```

## Usage

### Basic Chat

```php
use Llm;

// Simple chat - returns string
$response = Llm::chat('Explain transformers in simple terms.');

// Get full response object with metadata
$response = Llm::chatWithResponse('Explain transformers in simple terms.');
echo $response->content;
echo $response->model;
echo $response->latencyMs;
```

### Switching Providers

```php
use Llm;

// Use default provider (from config)
$response = Llm::chat('Hello!');

// Switch to Anthropic for this request
$response = Llm::using('anthropic')->chat('Write a Laravel policy example.');

// Switch to dummy for offline development
$response = Llm::using('dummy')->chat('Test message');

// Use LM Studio for local models
$response = Llm::using('lmstudio')->chat('Hello from local LLM!');
```

### Override Model Per Request

```php
use Llm;

$response = Llm::chat('Explain queues in Laravel.', [
    'model' => 'gpt-4.1',
    'temperature' => 0.7,
    'max_tokens' => 1000,
]);
```

### System Prompts

```php
use Llm;

$response = Llm::chat('What is 2 + 2?', [
    'system' => 'You are a helpful math tutor. Always explain your reasoning.',
]);
```

### Image Generation

```php
use Llm;

// Generate an image
$image = Llm::image()->generate([
    'prompt' => 'A minimalist logo for a Laravel AI package',
    'size' => '1024x1024',
]);

echo $image->url;

// Or use the convenience method
$image = Llm::generateImage([
    'prompt' => 'A futuristic cityscape',
    'size' => '512x512',
    'quality' => 'hd',
]);
```

### Listing Available Models

```php
use Llm;

// Get available models from OpenAI
$client = Llm::client('openai');
$models = $client->getAvailableModels();
print_r($models);
// ['gpt-4.1-mini', 'gpt-4.1', 'dall-e-3', ...]

// Check if the API is accessible
if ($client->isAvailable()) {
    echo "OpenAI API is accessible!";
}

// Same works for LM Studio
$lmClient = Llm::client('lmstudio');
if ($lmClient->isAvailable()) {
    $localModels = $lmClient->getAvailableModels();
    print_r($localModels);
}
```

### Using LM Studio (Local LLMs)

LM Studio allows you to run open-source LLMs locally. Perfect for development, testing, or privacy-sensitive applications.

**Setup:**
1. Download [LM Studio](https://lmstudio.ai/)
2. Load a model (e.g., Llama, Mistral, Phi)
3. Start the local server (default: `http://localhost:1234`)

**Usage:**
```php
use Llm;

// Basic chat with local model
$response = Llm::using('lmstudio')->chat('Explain Laravel middleware.');

// Check if LM Studio is running
$client = Llm::using('lmstudio')->client();
if ($client->isAvailable()) {
    echo "LM Studio is running!";
}

// List available models
$models = $client->getAvailableModels();
print_r($models);

// Use a specific local model
$response = Llm::using('lmstudio')->chat('Hello!', [
    'model' => 'mistral-7b-instruct',
    'temperature' => 0.7,
]);
```

**Set as default for local development:**
```env
LLM_SUITE_DEFAULT=lmstudio
```

### Working with Message History

```php
use Llm;

$response = Llm::chat('What is the capital of France?', [
    'messages' => [
        ['role' => 'system', 'content' => 'You are a geography expert.'],
        ['role' => 'user', 'content' => 'What continent is Brazil in?'],
        ['role' => 'assistant', 'content' => 'Brazil is in South America.'],
        ['role' => 'user', 'content' => 'What is the capital of France?'],
    ],
]);
```

## Testing

### Using Laravel HTTP Fakes

The simplest approach is to use Laravel's built-in HTTP faking:

```php
use Illuminate\Support\Facades\Http;
use Llm;

Http::fake([
    'api.openai.com/*' => Http::response([
        'id' => 'chatcmpl-test',
        'model' => 'gpt-4.1-mini',
        'choices' => [
            ['message' => ['content' => 'Fake response']],
        ],
    ]),
]);

$response = Llm::chat('Test');
$this->assertEquals('Fake response', $response);
```

### Using LlmFake

For more control, use the built-in fake helper:

```php
use Llm;

// Set up the fake
$fake = Llm::fake()
    ->shouldReturnChat('Hello world')
    ->shouldReturnImage('https://example.com/image.png');

// Make requests
$chatResponse = Llm::chat('Hi there');
$imageResponse = Llm::image()->generate(['prompt' => 'A cat']);

// Assert requests were made
$fake->assertChatSent('Hi there');
$fake->assertImageSent('A cat');
$fake->assertChatCount(1);
$fake->assertImageCount(1);
```

### Using the Dummy Provider

You can also use the dummy provider directly in your tests:

```php
use Llm;

// Switch to dummy provider
$response = Llm::using('dummy')->chat('Test message');
// Returns: "This is a dummy response to: Test message"
```

## Extending with Custom Drivers

You can register custom drivers for other LLM providers:

```php
use Oziri\LlmSuite\Facades\Llm;
use Oziri\LlmSuite\Contracts\ChatClient;
use Oziri\LlmSuite\Support\ChatResponse;

// Create your custom client
class MyCustomClient implements ChatClient
{
    public function __construct(protected array $config) {}

    public function chat(string $prompt, array $options = []): ChatResponse
    {
        // Your implementation here
        return new ChatResponse(
            content: 'Response from custom provider',
            raw: [],
            model: 'custom-model',
        );
    }
}

// Register the driver (in a service provider)
Llm::extend('custom', function (array $config) {
    return new MyCustomClient($config);
});

// Add to config/llm-suite.php
'providers' => [
    'my-custom' => [
        'driver' => 'custom',
        'api_key' => env('CUSTOM_API_KEY'),
    ],
],

// Use it
$response = Llm::using('my-custom')->chat('Hello!');
```

## Available Methods

### Facade Methods

| Method | Description |
|--------|-------------|
| `Llm::chat($prompt, $options)` | Send a chat message, returns string |
| `Llm::chatWithResponse($prompt, $options)` | Send a chat message, returns ChatResponse |
| `Llm::using($provider)` | Switch to a different provider |
| `Llm::image()` | Get the image client |
| `Llm::generateImage($params)` | Generate an image |
| `Llm::extend($driver, $callback)` | Register a custom driver |
| `Llm::fake()` | Create a fake for testing |
| `Llm::getProviders()` | List available providers |
| `Llm::getDefaultProvider()` | Get the default provider name |
| `Llm::client($name)` | Get the underlying client instance |

### Client Methods (OpenAI, LM Studio)

You can access the underlying client instance using `Llm::client('provider')` to call provider-specific methods:

```php
$client = Llm::client('openai');    // or 'lmstudio'
```

| Method | Description |
|--------|-------------|
| `$client->isAvailable()` | Check if the API/server is accessible |
| `$client->getAvailableModels()` | List available models from the provider |

### ChatResponse Properties

| Property | Type | Description |
|----------|------|-------------|
| `content` | string | The response text |
| `raw` | array | Raw API response data |
| `model` | string\|null | Model used for the request |
| `id` | string\|null | Request ID from the provider |
| `latencyMs` | float\|null | Request latency in milliseconds |

### ImageResponse Properties

| Property | Type | Description |
|----------|------|-------------|
| `url` | string\|null | URL of the generated image |
| `base64` | string\|null | Base64 encoded image data |
| `raw` | array | Raw API response data |
| `revisedPrompt` | string\|null | Revised prompt (if modified by provider) |

## Roadmap

- [ ] Streaming support
- [ ] Tool/Function calling
- [ ] Embeddings API
- [ ] RAG helpers
- [x] LM Studio support (local LLMs)
- [ ] Additional providers (Gemini, Groq, Ollama)
- [ ] Rate limiting
- [ ] Caching layer

## License

MIT License. See [LICENSE](LICENSE) for details.

