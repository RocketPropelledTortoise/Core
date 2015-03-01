<?php namespace Rocket\Taxonomy;

use Illuminate\Support\Facades\Cache;
use Rocket\Translation\Model\Language;
use Rocket\Translation\I18NFacade as I18N;
use Rocket\Taxonomy\Model\Vocabulary;
use Rocket\Taxonomy\Facade as T;

class HierarchyTest extends \Rocket\Utilities\TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->packagesToTest(['translations', 'taxonomy']);

        Language::insert(['name' => 'Français', 'iso' => 'fr']);
        Language::insert(['name' => 'English', 'iso' => 'en']);
        Cache::flush();
    }

    protected function getPackageProviders($app)
    {
        return [
            '\Rocket\Taxonomy\ServiceProvider',
            '\Rocket\Translation\TranslationServiceProvider'
        ];
    }

    public function testAddParent()
    {
        I18N::setLanguage('en');
        Vocabulary::insert(['name' => 'Family', 'machine_name' => 'family', 'hierarchy' => 2, 'translatable' => true]);
        $vid = T::vocabulary('family');

        $family = [
            'dad' => T::getTermId('Dad', $vid),
            'me' => T::getTermId('Me', $vid),
        ];

        $me = T::getTerm($family['me']);
        $me->addParent($family['dad']);

        $this->assertEquals([[$family['dad'], $family['me']]], T::getAncestryPaths($family['me']));
        $this->assertEquals([[$family['me'], $family['dad']]], T::getDescentPaths($family['dad']));
    }
}
