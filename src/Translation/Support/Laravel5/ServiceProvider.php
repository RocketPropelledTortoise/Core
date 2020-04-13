<?php namespace Rocket\Translation\Support\Laravel5;

use Illuminate\Foundation\Application;
use Rocket\Translation\Commands\GenerateFiles;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    protected function registerManager()
    {
        $this->app->alias('i18n', \Rocket\Translation\I18NInterface::class);
        $this->app->singleton(
            'i18n',
            function (Application $app) {
                return $app->make(\Rocket\Translation\I18N::class);
            }
        );
    }

    protected function registerLanguageChangeRoute()
    {
        $this->app['router']->get(
            'lang/{lang}',
            function ($lang) {
                try {
                    $this->app['i18n']->setLanguageForSession($lang);
                } catch (\RuntimeException $e) {
                    $this->app['session']->flash('error', t('Cette langue n\'est pas disponible'));
                }

                return $this->app['redirect']->back();
            }
        );
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerManager();

        $this->registerLanguageChangeRoute();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['i18n'];
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../../migrations');

        $this->commands([GenerateFiles::class]);
    }
}
