<?php namespace Rocket\Taxonomy;

use Cache;
use Rocket\Taxonomy\Facade as T;
use Rocket\Taxonomy\Model\Vocabulary;
use Rocket\Translation\Model\Language;

class VocabularyTest extends \Rocket\Utilities\TestCase
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
        return ['\Rocket\Taxonomy\ServiceProvider'];
    }

    public function testNoVocabularies()
    {
        $this->assertEmpty(T::vocabularies());
    }

    public function testOneVocabulary()
    {
        Vocabulary::insert(['name' => 'Test', 'machine_name' => 'test', 'hierarchy' => 0, 'translatable' => false]);

        $this->assertEquals(1, count(T::vocabularies()));
        $this->assertEquals('test', T::vocabulary(1));
        $this->assertEquals(1, T::vocabulary('test'));
    }

    public function testTranslatable()
    {
        Vocabulary::insert(['name' => 'Test', 'machine_name' => 'test', 'hierarchy' => 0, 'translatable' => true]);
        Vocabulary::insert(['name' => 'Test2', 'machine_name' => 'test2', 'hierarchy' => 0, 'translatable' => false]);

        $this->assertTrue(T::isTranslatable(1));
        $this->assertFalse(T::isTranslatable('test2'));
    }
}
