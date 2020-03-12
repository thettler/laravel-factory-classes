<?php

namespace Thettler\LaravelFactoryClasses;

use Illuminate\Support\ServiceProvider;
use Thettler\LaravelFactoryClasses\Commands\CreateFactoryClassCommand;

class FactoryClassServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/factory-classes.php' => config_path('factory-classes.php'),
            ], 'config');

            // Registering package commands.
            $this->commands([
                CreateFactoryClassCommand::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/factory-classes.php', 'factory-classes');
    }
}
