<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $this->forceTestingEnvironment();

        $app = require Application::inferBasePath().'/bootstrap/app.php';

        $this->traitsUsedByTest = array_flip(class_uses_recursive(static::class));

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    private function forceTestingEnvironment(): void
    {
        $environment = [
            'APP_ENV' => 'testing',
            'APP_DEBUG' => 'true',
            'APP_URL' => 'http://localhost',
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => ':memory:',
            'CACHE_STORE' => 'array',
            'SESSION_DRIVER' => 'array',
            'QUEUE_CONNECTION' => 'sync',
            'MAIL_MAILER' => 'array',
            'PULSE_ENABLED' => 'false',
        ];

        foreach ($environment as $key => $value) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}
