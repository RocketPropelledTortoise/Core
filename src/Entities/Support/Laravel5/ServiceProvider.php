<?php namespace Rocket\Entities\Support\Laravel5;

/**
 * Laravel5 Implementation of the Entities System
 */

use Illuminate\Support\Facades\Config;
use Rocket\Entities\Entity;

/**
 * This is a Service Provider for Laravel 5
 */
class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->publishes(
            [
                __DIR__ . '/config.php' => config_path('rocket_entities.php'),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        Entity::$types = Config::get('rocket_entities.field_types');
        $this->loadMigrationsFrom(__DIR__.'/../../migrations');
    }
}
