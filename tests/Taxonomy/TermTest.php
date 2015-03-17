<?php namespace Rocket\Taxonomy;

use Illuminate\Support\Facades\Cache;
use Rocket\Taxonomy\Support\Laravel5\Facade as T;
use Rocket\Taxonomy\Model\Vocabulary;
use Rocket\Translation\Support\Laravel5\Facade as I18N;
use Rocket\Translation\Model\Language;

class TermTest extends \Rocket\Utilities\TestCase
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
            '\Rocket\Taxonomy\Support\Laravel5\ServiceProvider',
            '\Rocket\Translation\Support\Laravel5\ServiceProvider'
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

    public function testSerializeAndExport()
    {
        I18N::setLanguage('fr');

        $term = new Term($this->exampleData());
        $this->assertEquals('Un Test', unserialize(serialize($term))->title());

        eval('$term2 = ' . var_export($term, true) . ';');
        $this->assertEquals('Un Test', $term2->title());
    }

    public function testTitle()
    {
        I18N::setLanguage('fr');

        $term = new Term($this->exampleData());

        $this->assertEquals('Un Test', strval($term));
        $this->assertEquals('Un Test', $term->title());
        $this->assertEquals('Un Test', $term['title']);
        $this->assertEquals('A Test', $term->title('en'));
    }

    public function testDescription()
    {
        I18N::setLanguage('fr');

        $term = new Term($this->exampleData());

        $this->assertEquals('Une Description', $term->description());
        $this->assertEquals('Une Description', $term['description']);
        $this->assertEquals('A Description', $term->description('en'));
        $this->assertEquals('', $term->description('cn'), 'requesting a non existing language should return an empty string');
    }

    public function testEmptyTerm()
    {
        $term = new Term([]);

        $this->assertEquals('', strval($term));
        $this->assertEquals('', $term['title']);
        $this->assertEquals('', $term->title());
        $this->assertEquals('', $term['description']);
        $this->assertEquals('', $term->description());
    }

    public function testID()
    {
        I18N::setLanguage('fr');

        $data = $this->exampleData();
        $term = new Term($data);

        $this->assertEquals($data['term_id'], $term->id());
        $this->assertEquals($data['term_id'], $term['term_id']);
    }

    public function testKindaUselessFunctions()
    {
        $term = new Term($this->exampleData());

        $term['term_id'] = 401;
        $this->assertEquals(401, $term['term_id']);

        $this->assertTrue(isset($term['term_id']));

        unset($term['term_id']);

        $this->assertEquals('', $term['term_id']);
    }

    public function testOneLanguage()
    {
        $term = new Term([
            'has_translations' => false,
            'lang' => ['title' => 'The Test', 'description' => 'Une Description'],
        ]);

        $this->assertEquals('The Test', $term->title());
        $this->assertEquals('The Test', $term['title']);
        $this->assertEquals('Une Description', $term->description());
        $this->assertEquals('Une Description', $term['description']);
        $this->assertTrue($term->translated());
        $this->assertTrue($term->translated('en'));
        $this->assertTrue($term->translated('fr'));
    }

    public function testTypes()
    {
        $term = new Term($this->exampleData());

        $this->assertEquals(T::TERM_CONTENT, $term->getType());
        $this->assertFalse($term->isSubcategory());


        $term = new Term(['type' => T::TERM_CATEGORY]);
        $this->assertEquals(T::TERM_CATEGORY, $term->getType());
        $this->assertTrue($term->isSubcategory());
    }

    public function testTranslated()
    {
        I18N::setLanguage('fr');

        $term = new Term($this->exampleData());

        $this->assertTrue($term->translated());
        $this->assertTrue($term->translated('en'));
        $this->assertFalse($term->translated('de'));
        $this->assertFalse($term->translated('cn'), 'non existing entries should return false');
    }

    public function testTranslatedOnRealTerm()
    {
        I18N::setLanguage('en');
        Vocabulary::insert(['name' => 'Test', 'machine_name' => 'test', 'hierarchy' => 0, 'translatable' => true]);

        $original_title = 'Test term original content';

        $id = T::getTermId($original_title, 'test');
        $term = T::getTerm($id);

        $this->assertEquals($original_title, $term->title());
        $this->assertEquals('', $term->description());
        $this->assertTrue($term->translated());

        $this->assertEquals($original_title, $term->title('fr'));
        $this->assertEquals('', $term->description('fr'));
        $this->assertFalse($term->translated('fr'));
    }

    public function testEdit()
    {
        I18N::setLanguage('en');
        Vocabulary::insert(['name' => 'Test', 'machine_name' => 'test', 'hierarchy' => 0, 'translatable' => true]);

        $original_title = 'Test term original content';
        $new_title = 'Test term new content';
        $new_description = 'A description';
        $new_title_fr = 'Terme de test, nouveau contenu';
        $new_description_fr = 'Une description';

        $id = T::getTermId($original_title, 'test');
        $term = T::getTerm($id);

        $this->assertEquals($original_title, $term->title());
        $this->assertEquals('', $term->description());
        $this->assertTrue($term->translated());

        $this->assertEquals($original_title, $term->title('fr'));
        $this->assertEquals('', $term->description('fr'));
        $this->assertFalse($term->translated('fr'));

        $en = $term->editLanguage('en');
        $en->title = $new_title;
        $en->description = $new_description;
        $en->save();

        $fr = $term->editLanguage('fr');
        $fr->title = $new_title_fr;
        $fr->description = $new_description_fr;
        $fr->save();

        $this->assertEquals($new_title, $term->title());
        $this->assertEquals($new_description, $term->description());
        $this->assertTrue($term->translated());

        $this->assertEquals($new_title_fr, $term->title('fr'));
        $this->assertEquals($new_description_fr, $term->description('fr'));
        $this->assertTrue($term->translated('fr'));

        $term_retrieved = T::getTerm($id);

        $this->assertEquals($new_title, $term_retrieved->title());
        $this->assertEquals($new_description, $term_retrieved->description());
        $this->assertTrue($term_retrieved->translated());

        $this->assertEquals($new_title_fr, $term_retrieved->title('fr'));
        $this->assertEquals($new_description_fr, $term_retrieved->description('fr'));
        $this->assertTrue($term_retrieved->translated('fr'));
    }

    /**
     * @expectedException \Rocket\Taxonomy\Exception\UndefinedLanguageException
     */
    public function testUndefinedLanguage()
    {
        I18N::setLanguage('fr');

        $term = new Term($this->exampleData());

        $term->editLanguage('cn');
    }
}
