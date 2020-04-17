<?php namespace Rocket\Taxonomy\Support\Laravel5;

use Illuminate\Contracts\Support\DeferrableProvider;
use Rocket\Taxonomy\Repositories\TermRepositoryInterface;
use Rocket\Taxonomy\Repositories\TermRepository;
use Rocket\Taxonomy\Repositories\TermHierarchyRepositoryInterface;
use Rocket\Taxonomy\Repositories\TermHierarchyRepository;
use Rocket\Taxonomy\Taxonomy;
use Rocket\Taxonomy\TaxonomyInterface;

/**
 * Taxonomy Service Provider
 */
class ServiceProvider extends \Illuminate\Support\ServiceProvider implements DeferrableProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(TermRepositoryInterface::class, TermRepository::class);
        $this->app->bind(TermHierarchyRepositoryInterface::class, TermHierarchyRepository::class);
        $this->app->bind(TaxonomyInterface::class, Taxonomy::class);
        $this->app->alias('i18n', \Rocket\Translation\Taxonomy::class);
        $this->app->singleton(
            'taxonomy',
            function ($app) {
                return $app->make(Taxonomy::class);
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
        return [
            'taxonomy',
            TaxonomyInterface::class,
            Taxonomy::class,
            TermRepositoryInterface::class,
            TermRepository::class,
            TermHierarchyRepositoryInterface::class,
            TermHierarchyRepository::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../../migrations');
    }
}
