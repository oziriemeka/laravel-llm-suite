<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default LLM Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default LLM provider that will be used when
    | calling Llm::chat() or other methods without explicitly specifying
    | a provider via Llm::using('provider_name').
    |
    */

    'default' => env('LLM_SUITE_DEFAULT', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | LLM Providers
    |--------------------------------------------------------------------------
    |
    | Here you may configure the LLM providers for your application. You may
    | configure as many providers as you wish, and you may even configure
    | multiple providers of the same driver.
    |
    */

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

        'dummy' => [
            'driver' => 'dummy',
            // Optional: set default responses for testing
            // 'chat_response' => 'This is a test response.',
            // 'image_url' => 'https://example.com/test-image.png',
        ],

    ],

];

