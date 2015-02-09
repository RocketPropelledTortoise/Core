<?php namespace Rocket\Entities;

use Illuminate\Support\ServiceProvider;

class EntitiesServiceProvider extends ServiceProvider
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
                __DIR__ . '/config/rocket_entities.php' => config_path('rocket_entities.php'),
            ]
        );
    }
}
