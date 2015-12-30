<?php namespace Rocket\Translation\Support\Laravel5;

use Illuminate\Foundation\Application;
use Rocket\Translation\Commands\GenerateFiles;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    protected function registerManager()
    {
        $this->app['i18n'] = $this->app->share(
            function (Application $app) {
                return $app->make('Rocket\Translation\I18N');
            }
        );
    }

    protected function registerLanguageChangeRoute()
    {
        $this->app['router']->get(
            'lang/{lang}',
            function ($lang) {
                if (!$this->app['i18n']->setCurrentLanguage($lang)) {
                    $this->app['session']->flash('error', t('Cette langue n\'est pas disponible'));
                }

                return $this->app['redirect']->back();
            }
        );
    }

    protected function registerCommand()
    {
        $this->app->singleton(
            'command.rocket_language_generate',
            function () {
                return new GenerateFiles;
            }
        );

        $this->commands('command.rocket_language_generate');
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

        $this->registerCommand();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['i18n', 'command.rocket_language_generate'];
    }
}
