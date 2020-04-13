<?php namespace Rocket\Taxonomy;

use Illuminate\Support\Facades\Cache;
use Rocket\Taxonomy\Model\Vocabulary;
use Rocket\Taxonomy\Support\Laravel5\Facade as T;
use Rocket\Translation\Model\Language;
use Rocket\Translation\Support\Laravel5\Facade as I18N;

class TaxonomyTest extends \Tests\DBTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Language::insert(['name' => 'Deutsch', 'iso' => 'de']);
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

    public function testGetLanguage()
    {
        I18N::setLanguage('en');
        $en_id = I18N::languages('en', 'id');
        $fr_id = I18N::languages('fr', 'id');

        Vocabulary::insert(['name' => 'Tag', 'machine_name' => 'tag', 'hierarchy' => 0, 'translatable' => true]);
        Vocabulary::insert(['name' => 'Artist', 'machine_name' => 'artist', 'hierarchy' => 0, 'translatable' => false]);

        $this->assertEquals(1, T::getLanguage(T::vocabulary('artist')));
        $this->assertEquals($en_id, T::getLanguage(T::vocabulary('tag')));
        $this->assertEquals($fr_id, T::getLanguage(T::vocabulary('tag'), $fr_id));
    }

    public function testSearchTerm()
    {
        I18N::setLanguage('en');
        Vocabulary::insert(['name' => 'Tag', 'machine_name' => 'tag', 'hierarchy' => 0, 'translatable' => true]);
        $vid = T::vocabulary('tag');

        $id = T::getTermId('TDD', $vid);

        $this->assertEquals($id, T::searchTerm('TDD', $vid));
        $this->assertNull(T::searchTerm('', $vid));
        $this->assertNull(T::searchTerm('Development', $vid));
    }

    public function testExcludedSearchTerm()
    {
        I18N::setLanguage('en');
        Vocabulary::insert(['name' => 'Tag', 'machine_name' => 'tag', 'hierarchy' => 0, 'translatable' => true]);
        $vid = T::vocabulary('tag');

        $idTDD = T::getTermId('TDD', $vid);
        $idPHP = T::getTermId('TDD PHP', $vid);

        $this->assertEquals($idTDD, T::searchTerm('TDD', $vid));
        $this->assertNull(T::searchTerm('TDD', $vid, null, [$idTDD]));
    }

    public function testGetTermID()
    {
        I18N::setLanguage('en');
        Vocabulary::insert(['name' => 'Tag', 'machine_name' => 'tag', 'hierarchy' => 0, 'translatable' => true]);
        $vid = T::vocabulary('tag');

        $idTDD = T::getTermId('TDD', $vid);
        $idPHP = T::getTermId('TDD PHP', $vid);

        $this->assertEquals($idTDD, T::getTermId('TDD', $vid));
    }

    public function testEmptyStringReturnsNothing()
    {
        I18N::setLanguage('en');
        Vocabulary::insert(['name' => 'Tag', 'machine_name' => 'tag', 'hierarchy' => 0, 'translatable' => true]);
        $vid = T::vocabulary('tag');

        $this->assertFalse(T::getTermId('', $vid));
    }

    public function testCreateSubcategory()
    {
        I18N::setLanguage('en');
        Vocabulary::insert(['name' => 'Tag', 'machine_name' => 'tag', 'hierarchy' => 0, 'translatable' => true]);
        $vid = T::vocabulary('tag');

        $idTDD = T::getTermId('TDD', $vid, null, T::TERM_CATEGORY);
        $idPHP = T::getTermId('TDD PHP', $vid);

        $this->assertTrue(T::getTerm($idTDD)->isSubcategory());
        $this->assertFalse(T::getTerm($idPHP)->isSubcategory());
    }

    public function testGetTerm()
    {
        I18N::setLanguage('en');
        Vocabulary::insert(['name' => 'Tag', 'machine_name' => 'tag', 'hierarchy' => 0, 'translatable' => true]);
        $vid = T::vocabulary('tag');

        $idTDD = T::getTermId('TDD', $vid, null, T::TERM_CATEGORY);
        $idPHP = T::getTermId('TDD PHP', $vid);

        $this->assertEquals('TDD', T::getTerm($idTDD)->title());
        $this->assertEquals('TDD', T::getTerm($idTDD)->title()); //for the cache ...
        $this->assertNull(T::getTerm(100));
    }

    public function testGetVocabularyTerms()
    {
        I18N::setLanguage('en');

        Vocabulary::insert(['name' => 'Tag', 'machine_name' => 'tag', 'hierarchy' => 0, 'translatable' => true]);

        $terms = [
            'tag' => [
                'PHP',
                'Development',
                'Design Patterns',
                'TDD',
            ],
        ];

        $ids = T::getTermIds($terms);

        $this->assertCount(4, $ids);
        $this->assertEquals($ids, T::getTermsForVocabulary(T::vocabulary('tag')));
    }

    public function testInsertTerms()
    {
        I18N::setLanguage('en');

        Vocabulary::insert(['name' => 'Tag', 'machine_name' => 'tag', 'hierarchy' => 0, 'translatable' => true]);
        Vocabulary::insert(['name' => 'Artist', 'machine_name' => 'artist', 'hierarchy' => 0, 'translatable' => false]);

        $terms = [
            'tag' => [
                'PHP',
                'Development',
                'Design Patterns',
                'TDD',
            ],
            'artist' => [
                'Blood Red Shoes',
                'Muse',
                'Nirvana',
            ],
        ];

        $ids = T::getTermIds($terms);
        $this->assertCount(7, $ids);

        $getIds = T::getTermIds($terms);
        $this->assertCount(7, $getIds);

        $this->assertEquals($ids, $getIds);
    }
}
