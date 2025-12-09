<?php

declare(strict_types=1);

namespace Oziri\LlmSuite;

use Illuminate\Support\ServiceProvider;
use Oziri\LlmSuite\Managers\LlmManager;

/**
 * Laravel service provider for LLM Suite.
 */
class LlmSuiteServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/llm-suite.php',
            'llm-suite'
        );

        $this->app->singleton(LlmManager::class, function ($app) {
            return new LlmManager($app['config']->get('llm-suite'));
        });

        $this->app->alias(LlmManager::class, 'llm-suite');
    }

    /**
     * Bootstrap the service provider.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            // Publish config
            $this->publishes([
                __DIR__ . '/../config/llm-suite.php' => config_path('llm-suite.php'),
            ], 'llm-suite-config');

            // Publish migrations
            $this->publishes([
                __DIR__ . '/../database/migrations/2024_01_01_000000_create_llm_conversations_table.php' => database_path('migrations/2024_01_01_000000_create_llm_conversations_table.php'),
            ], 'llm-suite-migrations');

            // Publish both config and migrations together
            $this->publishes([
                __DIR__ . '/../config/llm-suite.php' => config_path('llm-suite.php'),
                __DIR__ . '/../database/migrations/2024_01_01_000000_create_llm_conversations_table.php' => database_path('migrations/2024_01_01_000000_create_llm_conversations_table.php'),
            ], 'llm-suite');
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            LlmManager::class,
            'llm-suite',
        ];
    }
}

