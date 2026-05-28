<?php

declare(strict_types=1);

namespace Ginkelsoft\DataRightToBeForgotten;

use Ginkelsoft\DataRightToBeForgotten\Concerns\Forgettable;
use Ginkelsoft\DataRightToBeForgotten\Console\ForgetSubjectCommand;
use Ginkelsoft\DataRightToBeForgotten\Contracts\Forgettable as ForgettableContract;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider for the Laravel Data Right to Be Forgotten package.
 *
 * Responsibilities:
 * - Merge and publish the package configuration (config/forget.php).
 * - Publish and load the `forget_log` migration.
 * - Register the `retention:forget` Artisan command.
 *
 * Typical installation:
 *
 *   composer require ginkelsoft/laravel-data-right-to-be-forgotten
 *   php artisan vendor:publish --tag=forget-config
 *   php artisan vendor:publish --tag=forget-migrations
 *   php artisan migrate
 *
 * After installation, any model that uses {@see Forgettable} and
 * implements {@see ForgettableContract} can declare a subject-driven
 * erasure policy and be processed by `retention:forget {subject}`.
 *
 * The command name keeps the `retention:` prefix for backwards
 * compatibility with the monolithic v1.x `ginkelsoft/laravel-data-retention`
 * package.
 */
class DataRightToBeForgottenServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/forget.php',
            'forget'
        );
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/forget.php' => config_path('forget.php'),
        ], 'forget-config');

        $timestamp = date('Y_m_d_His');
        $this->publishes([
            __DIR__.'/../database/migrations/create_forget_log_table.php' => database_path("migrations/{$timestamp}_create_forget_log_table.php"),
        ], 'forget-migrations');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ForgetSubjectCommand::class,
            ]);
        }
    }
}
