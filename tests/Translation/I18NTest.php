<?php namespace Rocket\Taxonomy;

use Illuminate\Support\Facades\Cache;
use Rocket\Translation\Model\Language;
use Rocket\Translation\Support\Laravel5\Facade as I18N;

class I18NTest extends \Rocket\Utilities\DBTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Language::insert(['name' => 'Français', 'iso' => 'fr']);
        Language::insert(['name' => 'English', 'iso' => 'en']);
        Cache::flush();
    }

    protected function getPackageProviders($app)
    {
        return [
            '\Rocket\Translation\Support\Laravel5\ServiceProvider'
        ];
    }

    public function testGetCurrentLanguage()
    {
        $this->assertEquals('en', I18N::getCurrent());
    }

    public function testCommandRunsGeneration()
    {
        I18N::shouldReceive('generate');

        $artisan = $this->app->make('Illuminate\Contracts\Console\Kernel');
        $artisan->call('rocket:generate_languages');
    }
}
