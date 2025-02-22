<?php

namespace Taitech\TravfdPhp\Providers;

// use function config_path;

use Illuminate\Support\ServiceProvider;
use Taitech\TravfdPhp\TraVfdClient;

class TraVfdServiceProvider extends ServiceProvider
{
    /**
     * Registers the TraVfdClient service with the application.
     *
     * This method is called during the application's bootstrapping process.
     * It performs the following tasks:
     *
     * 1. Merges the configuration from the 'tra_vfd.php' file into the application's configuration.
     * 2. Binds the TraVfdClient class as a singleton in the application's service container.
     * 3. Aliases the TraVfdClient class as 'tra-vfd' in the application's service container.
     *
     * These steps ensure that the TraVfdClient service is available throughout the application and can be
     * easily accessed using the 'tra-vfd' alias.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/tra_vfd.php', 'tra_vfd');

        $this->app->singleton(TraVfdClient::class, function ($app) {
            return new TraVfdClient();
        });

        $this->app->alias(TraVfdClient::class, 'tra-vfd');
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/tra_vfd.php' => config_path('tra_vfd.php'),
        ], 'config');
    }
}
