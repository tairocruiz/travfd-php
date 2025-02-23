<?php

namespace Taitech\TravfdPhp\Providers;

use Illuminate\Support\ServiceProvider;
use Taitech\TravfdPhp\TravfdClient;;

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
class TravfdServiceProvider extends ServiceProvider
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
        $this->mergeConfigFrom(__DIR__ . '/../../config/travfd.php', 'travfd');

        $this->app->singleton(TravfdClient::class, function ($app) {
            return new TravfdClient();
        });

        $this->app->alias(TravfdClient::class, 'travfd');
    }

    /**
     * Publishes the configuration file for the TraVfdClient service.
     *
     * This method is called during the application's bootstrapping process.
     * It publishes the 'travfd.php' configuration file from the package's
     * config directory to the application's config directory, allowing the
     * user to customize the configuration as needed.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/travfd.php' => config_path('travfd.php'),
        ], 'config');
    }
}
