<?php namespace Rocket\Entities\Support\Laravel5;

use Illuminate\Support\Facades\Config;
use Rocket\Entities\Entity;
use Rocket\Entities\EntityManager;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->publishes(
            [
                __DIR__ . '/config.php' => config_path('rocket_entities.php'),
            ]
        );

        $this->app->singleton(
            'entity_manager',
            function () {
                return new EntityManager;
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        Entity::$types = Config::get('rocket_entities.field_types');
    }
}
