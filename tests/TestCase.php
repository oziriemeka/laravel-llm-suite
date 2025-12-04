<?php

declare(strict_types=1);

namespace Oziri\LlmSuite\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Oziri\LlmSuite\LlmSuiteServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            LlmSuiteServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Llm' => \Oziri\LlmSuite\Facades\Llm::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('llm-suite.default', 'dummy');
        $app['config']->set('llm-suite.providers.dummy', [
            'driver' => 'dummy',
        ]);
    }
}

