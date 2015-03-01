<?php namespace Rocket\Taxonomy;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Rocket\Taxonomy\Facade as T;
use Rocket\Taxonomy\Model\TermContent;
use Rocket\Taxonomy\Model\Vocabulary;
use Rocket\Taxonomy\TaxonomyTrait;
use Rocket\Translation\Model\Language;
use Illuminate\Support\Facades\Schema;

class Post extends Model {
    use TaxonomyTrait;

    public $timestamps = false;
    public $fillable = ['content'];

    public static function createTable()
    {
        Schema::create(
            (new self)->getTable(),
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('content');
            }
        );
    }
}

class Media extends Model {
    use TaxonomyTrait;

    public $timestamps = false;
    public $fillable = ['file'];

    public static function createTable()
    {
        Schema::create(
            (new self)->getTable(),
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('file');
            }
        );
    }
}

class TaxonomyContentTest extends \Rocket\Utilities\TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->packagesToTest(['translations', 'taxonomy']);

        Language::insert(['name' => 'Français', 'iso' => 'fr']);
        Language::insert(['name' => 'English', 'iso' => 'en']);
        Cache::flush();
    }

    public function tearDown()
    {
        Schema::dropIfExists('media');
        Schema::dropIfExists('posts');
        parent::tearDown();
    }

    protected function getPackageProviders($app)
    {
        return [
            '\Rocket\Taxonomy\ServiceProvider',
            '\Rocket\Translation\TranslationServiceProvider'
        ];
    }

    public function testSetTerms()
    {
        Post::createTable();

        Vocabulary::insert(['name' => 'Tag', 'machine_name' => 'tag', 'hierarchy' => 0, 'translatable' => false]);


        $ids = T::getTermIds(['tag' => ['TDD', 'PHP', 'Add some tags']]);
        $post = new Post(['content' => 'a test post']);
        $post->save();
        $post->setTerms($ids);


        $ids2 = T::getTermIds(['tag' => ['TDD', 'HTML', 'JavaScript']]);
        $post2 = new Post(['content' => 'a seconds test post']);
        $post2->save();
        $post2->setTerms($ids2);

        $this->assertEquals(6, TermContent::count());
        $this->assertEquals($ids, TermContent::where('relationable_type', get_class($post))->where('relationable_id', $post->id)->lists('term_id'));
        $this->assertEquals($ids2, TermContent::where('relationable_type', get_class($post2))->where('relationable_id', $post2->id)->lists('term_id'));
    }

    public function testSetTermsOverride()
    {
        Post::createTable();

        Vocabulary::insert(['name' => 'Tag', 'machine_name' => 'tag', 'hierarchy' => 0, 'translatable' => false]);

        $post = new Post(['content' => 'a test post']);
        $post->save();

        //set initial terms
        $ids = T::getTermIds(['tag' => ['TDD', 'PHP', 'Add some tags']]);
        $post->setTerms($ids);

        //change terms
        $ids2 = T::getTermIds(['tag' => ['TDD', 'HTML', 'JavaScript']]);
        $post->setTerms($ids2);

        $this->assertEquals(3, TermContent::count());
        $this->assertEquals($ids2, TermContent::where('relationable_type', get_class($post))->where('relationable_id', $post->id)->lists('term_id'));
    }

    public function testGetTerms()
    {
        Post::createTable();

        Vocabulary::insert(['name' => 'Tag', 'machine_name' => 'tag', 'hierarchy' => 0, 'translatable' => false]);

        $post = new Post(['content' => 'a test post']);
        $post->save();

        $ids = T::getTermIds(['tag' => ['TDD', 'PHP', 'Add some tags']]);
        $post->setTerms($ids);

        //add a second entry to be sure we match only the first set
        $post2 = new Post(['content' => 'a seconds test post']);
        $post2->save();

        $ids2 = T::getTermIds(['tag' => ['TDD', 'HTML', 'JavaScript']]);
        $post2->setTerms($ids2);

        $postRetrieved = Post::find($post->id);

        //Assert
        $terms = $postRetrieved->getTerms('tag');
        $this->assertCount(3, $terms);
        $this->assertInstanceOf('\Rocket\Taxonomy\Term', $terms[0]);
        $this->assertEquals($ids, $terms->lists('term_id'));
    }

    public function testAddTermNoOverride()
    {
        Post::createTable();

        Vocabulary::insert(['name' => 'Tag', 'machine_name' => 'tag', 'hierarchy' => 0, 'translatable' => false]);

        $post = new Post(['content' => 'a test post']);
        $post->save();

        $ids = T::getTermIds(['tag' => ['TDD', 'PHP', 'Add some tags']]);
        $post->setTerms($ids);

        $ids[] = $newId = T::getTermId('JavaScript', 'tag');

        $post->addTerm($newId);
        $this->assertEquals(4, TermContent::count());
        $this->assertEquals($ids, TermContent::where('relationable_type', get_class($post))->where('relationable_id', $post->id)->lists('term_id'));
    }

    public function testSetNoDuplicate()
    {
        Post::createTable();

        Vocabulary::insert(['name' => 'Tag', 'machine_name' => 'tag', 'hierarchy' => 0, 'translatable' => false]);

        $post = new Post(['content' => 'a test post']);
        $post->save();

        $ids = T::getTermIds(['tag' => ['TDD', 'PHP', 'Add some tags']]);
        $post->setTerms($ids);

        $newId = T::getTermId('TDD', 'tag');

        $post->addTerm($newId);
        $this->assertEquals(3, TermContent::count());
        $this->assertEquals($ids, TermContent::where('relationable_type', get_class($post))->where('relationable_id', $post->id)->lists('term_id'));
    }

    public function testSetTermsForOneVocabulary()
    {
        Post::createTable();

        Vocabulary::insert(['name' => 'Tag', 'machine_name' => 'tag', 'hierarchy' => 0, 'translatable' => false]);
        Vocabulary::insert(['name' => 'Artists', 'machine_name' => 'artist', 'hierarchy' => 0, 'translatable' => false]);

        $post = new Post(['content' => 'a test post']);
        $post->save();

        //add initial terms
        $tag_ids = T::getTermIds(['tag' => ['TDD', 'PHP', 'Add some tags']]);
        $artist_ids = T::getTermIds(['artist' => ['Mika', 'Elton John']]); //these should not be in the final list
        $post->setTerms(array_merge($artist_ids, $tag_ids));

        //add new terms
        $idsNew = T::getTermIds(['artist' => ['Muse', 'Blood Red Shoes', 'Ratatat']]);
        $post->setTerms($idsNew, 'artist');

        $all = TermContent::where('relationable_type', get_class($post))->where('relationable_id', $post->id)->lists('term_id');

        $this->assertCount(6, $all);
        $this->assertEquals(array_merge($tag_ids, $idsNew), $all);
    }

    /**
     * @expectedException \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function testExistenceOfTermsBeforeLinking()
    {
        Post::createTable();

        $post = new Post(['content' => 'a test post']);
        $post->save();

        $post->addTerm(101);
    }

    public function testEmpty()
    {
        Post::createTable();
        Media::createTable();

        Vocabulary::insert(['name' => 'Tag', 'machine_name' => 'tag', 'hierarchy' => 0, 'translatable' => false]);

        //add some tags to another post to make the test more interesting
        $post = new Post(['content' => 'a test post']);
        $post->save();
        $ids = T::getTermIds(['tag' => ['TDD', 'PHP', 'Add some tags']]);
        $post->setTerms($ids);

        $post2 = new Post(['content' => 'a seconds test post']);
        $post2->save();

        $this->assertEmpty($post2->getTerms('tag'));
    }

    public function testGetRelatedContents()
    {


        Post::createTable();
        Media::createTable();

        Vocabulary::insert(['name' => 'Tag', 'machine_name' => 'tag', 'hierarchy' => 0, 'translatable' => false]);

        $ids = T::getTermIds(['tag' => ['TDD', 'PHP', 'Add some tags']]);
        $post = new Post(['content' => 'a test post']);
        $post->save();
        $post->setTerms($ids);

        // add a second entry to be sure
        // we match only the first tags
        $ids2 = T::getTermIds(['tag' => ['TDD', 'HTML', 'JavaScript']]);
        $post2 = new Post(['content' => 'a seconds test post']);
        $post2->save();
        $post2->setTerms($ids2);

        // add an entry with a different type
        // to be sure we don't take it by mistake
        $ids = T::getTermIds(['tag' => ['TDD', 'PHP', 'Add some tags']]);
        $media = new Media(['file' => '/var/www/image.jpg']);
        $media->save();
        $media->setTerms($ids);


        $postWithPHP = Post::getAllByTermId(T::getTermId('PHP', 'tag'))->get();
        $this->assertEquals($post->id, $postWithPHP[0]->id);
        $this->assertInstanceOf(get_class($post), $postWithPHP[0]);

        $post2->setTerms($ids);

        $postsWithPHP = Post::getAllByTermId(T::getTermId('PHP', 'tag'))->select('posts.id')->lists('id');
        $this->assertEquals([$post->id, $post2->id], $postsWithPHP);
    }
}
