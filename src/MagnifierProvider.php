<?php

namespace Mariojgt\Magnifier;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Mariojgt\Magnifier\Commands\Republish;
use Mariojgt\Magnifier\Commands\Install;

class MagnifierProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Load some commands
        if ($this->app->runningInConsole()) {
            // Register the install and republish commands
            $this->commands([
                Republish::class,
                Install::class,
            ]);
        }

        // Load magnifier views
        $this->loadViewsFrom(__DIR__ . '/views', 'magnifier');
        // Load magnifier routes
        $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');
        // Load Migrations
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->publish();
    }

    public function publish()
    {
        // Publish the npm case we need to do soem developent
        $this->publishes([
            __DIR__ . '/../Publish/Npm/' => base_path()
        ]);

        // Publish the resource in case we need to compile
        $this->publishes([
            __DIR__ . '/../Publish/Resource/' => resource_path('vendor/Magnifier/')
        ]);

        // Publish the public folder
        $this->publishes([
            __DIR__ . '/../Publish/Public/' => public_path('build/')
        ]);

        // Publish the public folder
        $this->publishes([
            __DIR__ . '/../Publish/Config/' => config_path('')
        ]);
    }
}
