<?php namespace Rocket\Entities\Support\Laravel5;

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
}
