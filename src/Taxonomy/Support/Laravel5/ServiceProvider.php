<?php

/**
 * The Laravel 5 Service Provider for Taxonomies
 */
namespace Rocket\Taxonomy\Support\Laravel5;

/**
 * Taxonomy Service Provider
 */
class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * @var bool Indicates if loading of the provider is deferred.
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $prefix = 'Rocket\Taxonomy\Repositories';
        $this->app->bind("$prefix\TermRepositoryInterface", "$prefix\TermRepository");
        $this->app->bind("$prefix\TermHierarchyRepositoryInterface", "$prefix\TermHierarchyRepository");

        $this->app->singleton(
            'taxonomy',
            function ($app) {
                return $app->make('\Rocket\Taxonomy\Taxonomy');
            }
        );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['taxonomy'];
    }
}
