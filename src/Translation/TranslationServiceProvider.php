<?php namespace Rocket\Translation;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Rocket\Translation\Commands\GenerateFiles;

class TranslationServiceProvider extends ServiceProvider
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
        $this->app->bindShared(
            'command.rocket_language_generate',
            function ($app) {
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
        return array('i18n', 'command.rocket_language_generate');
    }
}
