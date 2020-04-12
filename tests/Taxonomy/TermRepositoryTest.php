<?php namespace Rocket\Taxonomy;

use Illuminate\Support\Facades\Cache;
use Rocket\Taxonomy\Model\Vocabulary;
use Rocket\Taxonomy\Support\Laravel5\Facade as T;
use Rocket\Translation\Model\Language;
use Rocket\Translation\Support\Laravel5\Facade as I18N;

class TermRepositoryTest extends \Rocket\Utilities\DBTestCase
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
            '\Rocket\Taxonomy\Support\Laravel5\ServiceProvider',
            '\Rocket\Translation\Support\Laravel5\ServiceProvider',
        ];
    }

    protected function exampleData()
    {
        return [
            'term_id' => 10,
            'lang_fr' => ['title' => 'Un Test', 'description' => 'Une Description', 'translated' => true],
            'lang_en' => ['title' => 'A Test', 'description' => 'A Description', 'translated' => true],
            'lang_de' => ['title' => 'A Test', 'description' => 'A Description', 'translated' => false],
        ];
    }

    public function testTranslated()
    {
        I18N::setLanguage('en');
        Vocabulary::insert(['name' => 'Tag', 'machine_name' => 'tag', 'hierarchy' => 0, 'translatable' => true]);
        $vid = T::vocabulary('tag');

        $idTDD = T::getTermId('TDD', $vid);

        $term = T::getTerm($idTDD);

        $this->assertEquals('TDD', $term->title());
        $this->assertTrue($term->translated());
        $this->assertEquals('TDD', $term->title('fr'));
        $this->assertFalse($term->translated('fr'));
    }

    public function testUntranslated()
    {
        I18N::setLanguage('en');
        Vocabulary::insert(['name' => 'Artist', 'machine_name' => 'artist', 'hierarchy' => 0, 'translatable' => false]);
        $vid = T::vocabulary('artist');

        $idTDD = T::getTermId('TDD', $vid);

        I18N::setLanguage('fr');
        $term = T::getTerm($idTDD);

        $this->assertEquals('TDD', $term->title());
        $this->assertTrue($term->translated());
        $this->assertEquals('TDD', $term->title('en'));
        $this->assertTrue($term->translated('en'));
    }
}
