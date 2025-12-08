<p align="center">
  <img src="https://raw.githubusercontent.com/OziriEmeka/laravel-llm-suite/main/assets/logo/laravel-llm-suite-logo.png" alt="Laravel LLM Suite">
</p>

# Laravel LLM Suite

A unified, driver-based Laravel toolkit for working with multiple LLM providers for chat, image generation, and more.

## Features

- **Unified API** - Same interface regardless of provider (OpenAI, Anthropic, LM Studio, etc.)
- **Driver Pattern** - Switch providers like Laravel's Storage or Mail systems
- **Conversation Management** - Automatic message history with session or database storage
- **Token Usage Tracking** - Monitor token consumption for cost management
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

Add your API keys to your `.env` file (Please keep keys and API Safe and do not share with anyone):

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
LMSTUDIO_PROTOCOL=http   # http or https
LMSTUDIO_HOST=127.0.0.1
LMSTUDIO_PORT=1234
LMSTUDIO_API_KEY=        # Optional - leave empty if not using authentication
LMSTUDIO_TIMEOUT=120
# LMSTUDIO_BASE_URL=     # Optional - override protocol/host/port with full URL

# Conversation Storage (optional - database is default)
LLM_CONVERSATION_DRIVER=database   # or 'session'
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
            'protocol' => env('LMSTUDIO_PROTOCOL', 'http'),
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

    // Conversation storage settings
    'conversation' => [
        'driver' => env('LLM_CONVERSATION_DRIVER', 'database'),
        'table' => 'llm_conversations',
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

### Conversations (Multi-turn Chat)

Build chatbots and maintain context across multiple messages:

```php
use Llm;

// Start a NEW conversation (auto-generates UUID)
$conversation = Llm::conversation();
$conversation->system('You are a helpful assistant.');

// Chat with automatic context - the LLM remembers previous messages!
$response = $conversation->chat('My name is John.');
$response = $conversation->chat('What is my name?'); // "Your name is John."

// Get the conversation ID for later use
$conversationId = $conversation->getId();
// e.g., "550e8400-e29b-41d4-a716-446655440000"
```

**Resume an existing conversation:**

```php
// Resume conversation using the saved ID
$conversation = Llm::conversation($conversationId);
$response = $conversation->chat('What else do you remember about me?');
```

**Use a specific provider for conversations:**

```php
$conversation = Llm::using('openai')->conversation();
// or
$conversation = Llm::using('lmstudio')->conversation();
```

**Practical API example:**

```php
// Start new chat
Route::post('/chat/new', function (Request $request) {
    $conversation = Llm::conversation();
    $conversation->system('You are a helpful assistant.');
    $response = $conversation->chat($request->input('message'));
    
    return [
        'conversation_id' => $conversation->getId(),
        'response' => $response->content,
        'tokens' => $response->tokenUsage->totalTokens,
    ];
});

// Continue existing chat
Route::post('/chat/{id}', function (Request $request, string $id) {
    $conversation = Llm::conversation($id);
    $response = $conversation->chat($request->input('message'));
    
    return [
        'response' => $response->content,
        'tokens' => $response->tokenUsage->totalTokens,
    ];
});
```

**Other conversation methods:**

```php
$conversation->getMessages();       // Get all messages
$conversation->getMessageCount();   // Count messages
$conversation->getSystemPrompt();   // Get system prompt
$conversation->clear();             // Clear history (keeps system prompt)
$conversation->delete();            // Delete entire conversation
$conversation->export();            // Export as array
```

**List all conversations:**

```php
use Llm;

// Get all conversation IDs
$conversationIds = Llm::conversations();
// ['550e8400-e29b-41d4-a716-446655440000', '6ba7b810-9dad-11d1-80b4-00c04fd430c8', ...]

// With database driver, you can also get metadata
$store = Llm::getConversationStore();
if ($store instanceof \Oziri\LlmSuite\ConversationStores\DatabaseStore) {
    $conversations = $store->allWithMetadata();
    // [['id' => '...', 'message_count' => 5, 'created_at' => '...', 'updated_at' => '...'], ...]
}
```

**Storage Drivers:**

| Driver | Storage | Best For |
|--------|---------|----------|
| `database` | Database table | Persistent storage, chat history (default) |
| `session` | Laravel session | Temporary chats, no database setup |

**Database Driver (Default):**

Conversations are stored in the database for persistent storage. Publish and run the migration:

```bash
php artisan vendor:publish --tag=llm-suite-migrations
php artisan migrate
```

This creates the `llm_conversations` table for storing conversation history.

**Session Driver:**

For temporary chats that don't need persistence (expires with session):

```env
LLM_CONVERSATION_DRIVER=session
```

No migration required for session driver.

### Token Usage

Track token consumption for cost monitoring:

```php
use Llm;

$response = Llm::chatWithResponse('Explain Laravel in one paragraph.');

// Access token usage
echo $response->tokenUsage->promptTokens;      // Input tokens
echo $response->tokenUsage->completionTokens;  // Output tokens
echo $response->tokenUsage->totalTokens;       // Total tokens

// Helper methods
echo $response->getTotalTokens();
echo $response->getPromptTokens();
echo $response->getCompletionTokens();

// As array
$usage = $response->tokenUsage->toArray();
// ['prompt_tokens' => 10, 'completion_tokens' => 50, 'total_tokens' => 60]
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
| `Llm::conversation($id)` | Start new or resume existing conversation |
| `Llm::conversations()` | Get all conversation IDs |

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
| `tokenUsage` | TokenUsage | Token usage statistics |

### TokenUsage Properties

| Property | Type | Description |
|----------|------|-------------|
| `promptTokens` | int | Number of tokens in the prompt/input |
| `completionTokens` | int | Number of tokens in the completion/output |
| `totalTokens` | int | Total tokens used |

**Methods:**
- `toArray()` - Convert to array
- `hasData()` - Check if usage data is available
- `TokenUsage::fromArray($data)` - Create from API response
- `TokenUsage::empty()` - Create empty instance

### ImageResponse Properties

| Property | Type | Description |
|----------|------|-------------|
| `url` | string\|null | URL of the generated image |
| `base64` | string\|null | Base64 encoded image data |
| `raw` | array | Raw API response data |
| `revisedPrompt` | string\|null | Revised prompt (if modified by provider) |

## Roadmap

- [x] LM Studio support (local LLMs)
- [x] Conversation management (session & database storage)
- [x] Token usage tracking
- [ ] Streaming support
- [ ] Tool/Function calling
- [ ] Embeddings API
- [ ] RAG helpers
- [ ] Additional providers (Gemini, Groq, Ollama)
- [ ] Rate limiting
- [ ] Caching layer

## License

MIT License. See [LICENSE](LICENSE) for details.

