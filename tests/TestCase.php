<?php

declare(strict_types=1);

namespace Ginkelsoft\DataRightToBeForgotten\Tests;

use Ginkelsoft\ComplianceCore\ComplianceCoreServiceProvider;
use Ginkelsoft\DataRightToBeForgotten\DataRightToBeForgottenServiceProvider;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * @param  Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            ComplianceCoreServiceProvider::class,
            DataRightToBeForgottenServiceProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('compliance.log_secret', 'test-log-secret');
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
