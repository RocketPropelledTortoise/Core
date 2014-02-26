<?php namespace Rocket\Translation;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class TranslationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        //$this->package('rocket/lang');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //Translation manager
        $this->app['i18n'] = $this->app->share(
            function (Application $app) {
                return $app->make('Rocket\Translation\I18N');
            }
        );

        //Change language
        $app = $this->app;
        $this->app['router']->get(
            'lang/{lang}',
            function ($lang) use ($app) {
                if (!$app['i18n']->setCurrentLanguage($lang)) {
                    $app['session']->flash('error', t('Cette langue n\'est pas disponible'));
                }

                return $app['redirect']->back();
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
        return array('i18n');
    }
}
