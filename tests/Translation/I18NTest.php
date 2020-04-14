<?php namespace Rocket\Taxonomy;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route as RouteFacade;
use Rocket\Translation\Model\Language;
use Rocket\Translation\Model\StringModel;
use Rocket\Translation\Model\Translation;
use Rocket\Translation\Support\Laravel5\Facade as I18N;

class I18NTest extends \Tests\DBTestCase
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
        I18N::shouldReceive('dumpCache');

        $this->artisan('rocket:generate_languages');
    }

    public function testGetDefaultContextWhenNoRoute()
    {
        $this->assertEquals('default', I18N::getContext());
    }

    public function testGetContextNamedRoute()
    {
        RouteFacade::get('user/{id}', 'UserController@profile')->name('user.profile');
        $route = RouteFacade::getRoutes()->match(Request::create('/user/24'));
        RouteFacade::shouldReceive('current')->andReturn($route);

        $this->assertEquals('user.profile', I18N::getContext());
    }

    public function testGetContextRouteController()
    {
        RouteFacade::get('user/{id}', 'UserController@profile');
        $route = RouteFacade::getRoutes()->match(Request::create('/user/24'));
        RouteFacade::shouldReceive('current')->andReturn($route);

        $this->assertEquals('UserController@profile', I18N::getContext());
    }

    public function testGetContextCallbackRoute()
    {
        RouteFacade::get('user/{id}', function ($id) {
            return 'User '.$id;
        });
        $route = RouteFacade::getRoutes()->match(Request::create('/user/24'));
        RouteFacade::shouldReceive('current')->andReturn($route);

        $this->assertEquals('user/{id}', I18N::getContext());
        $this->assertEquals('user/{id}', I18N::getContext());
    }

    public function testTranslate()
    {
        $keyString = 'My English String';

        $string = t($keyString);
        $this->assertEquals($keyString, $string);

        $string = t($keyString);
        $this->assertEquals($keyString, $string);
    }

    public function testTranslateInsertOtherLanguage()
    {
        $keyString = 'My English String';
        $frTranslation = 'En Français S\'il vous plait';

        $string = t($keyString);
        $this->assertEquals($keyString, $string);

        Translation::setTranslation(StringModel::getStringId(I18N::getContext(), $keyString), I18N::languages('fr', 'id'), $frTranslation);

        $string = t($keyString, [], null, 'fr');
        $this->assertEquals($frTranslation, $string);
    }

    public function testTranslateStartInOtherSourceLanguage()
    {
        $keyString = 'My English String';
        $frTranslation = 'En Français S\'il vous plait';

        // GIVEN we are in french currently
        I18N::setLanguageForRequest('fr');

        // WHEN we request a translation for the first time
        $string = t($keyString);

        // THEN we get the key as the translation
        $this->assertEquals($keyString, $string);

        // WHEN we get the english translation
        $string = t($keyString, [], null, 'en');
        // THEN We get the real translation
        $this->assertEquals($keyString, $string);

        // WHEN we set the translation
        Translation::setTranslation(StringModel::getStringId(I18N::getContext(), $keyString), I18N::languages('fr', 'id'), $frTranslation);

        // THEN the translation is retrieved correctly
        $string = t($keyString, [], null, 'fr');
        $this->assertEquals($frTranslation, $string);
    }
}
