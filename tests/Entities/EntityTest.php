<?php namespace Rocket\Entities;

use Rocket\Entities\Support\Laravel5\Facade as Entities;
use Rocket\Translation\Model\Language;

class CommentDemo extends Entity
{
    protected function getFields()
    {
        return [];
    }
}

class Demo extends Entity
{
    protected function getFields()
    {
        return [

            'title' => [
                'type' => 'string', //max width :: 255
            ],
            'titles' => [
                'type' => 'string',
                'max_items' => 4,
            ],
        ];
    }
}

class ReservedFields extends Entity
{
    protected function getFields()
    {
        return [
            'created_at' => [
                'type' => 'string', //max width :: 255
            ],
            'language_id' => [
                'type' => 'string',
                'max_items' => 4,
            ],
        ];
    }
}

class NonExistentType extends Entity
{
    protected function getFields()
    {
        return [
            'content' => [
                'type' => 'no_type', //max width :: 255
            ],
        ];
    }
}

class EntityTest extends \Rocket\Utilities\TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->packagesToTest(['translations', 'entities']);

        Language::insert(['name' => 'Français', 'iso' => 'fr']);
        Language::insert(['name' => 'English', 'iso' => 'en']);
    }

    protected function getPackageProviders($app)
    {
        return ['\Rocket\Entities\Support\Laravel5\ServiceProvider'];
    }

    public function testGetContentType()
    {
        $comment = new Demo(['language_id' => 1]);
        $this->assertEquals('demo', Entities::getContentType($comment));
    }

    public function testExtendedGetContentType()
    {
        $comment = new CommentDemo(['language_id' => 1]);
        $this->assertEquals('comment_demo', Entities::getContentType($comment));
    }

    public function testCreateSimpleEntity()
    {
        $first_lang = Language::pluck('id');

        $demo = new Demo();
        $demo->language_id = $first_lang;

        $this->assertEquals($first_lang, $demo->language_id);
    }

    public function testCreateSimpleEntityAndRetrieve()
    {
        $title = 'new Title';
        $first_lang = Language::pluck('id');

        $demo = new Demo();
        $demo->language_id = $first_lang;
        $demo->title = $title;

        $this->assertEquals($title, $demo->title);
    }

    public function testFieldCollectionResilience()
    {
        $demo = new Demo();

        $this->assertInstanceOf('\Rocket\Entities\FieldCollection', $demo->getField('titles'));

        $demo->titles[] = 'one title';

        $this->assertEquals(['one title'], $demo->titles->toArray());

        $demo->titles = [];

        $this->assertEquals([], $demo->titles->toArray());

        $this->assertInstanceOf(
            '\Rocket\Entities\FieldCollection',
            $demo->getField('titles'),
            'after reassignment, titles should still be a fieldCollection'
        );
    }

    public function testCreateSimpleEntityMultipleValues()
    {
        $title = 'new Title';
        $title2 = 'new Title2';
        $first_lang = Language::pluck('id');

        $demo = new Demo();
        $demo->language_id = $first_lang;

        $demo->titles = [];
        $demo->titles[] = $title;
        $demo->titles[] = $title2;

        $this->assertEquals($title, $demo->titles[0]);
        $this->assertEquals($title2, $demo->titles[1]);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetNonExistentField()
    {
        $demo = new Demo();
        $demo->foo = [];
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testSetNonExistentField()
    {
        $demo = new Demo();
        $foo = $demo->foo;
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testReservedFieldNames()
    {
        $test = new ReservedFields();
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testNonExistentFieldType()
    {
        $test = new NonExistentType();
    }

    public function testIsset()
    {
        $demo = new Demo();

        $demo->titles[] = 'one title';

        $this->assertTrue(isset($demo->titles[0]));
        $this->assertFalse(isset($demo->titles[4]));
    }

    public function testUnset()
    {
        $demo = new Demo();

        $demo->titles[] = 'one title';
        $demo->titles[] = 'two titles';

        unset($demo->titles[0]);

        $this->assertEquals(['two titles'], array_values($demo->titles->toArray()));
    }

    public function testCreateRevision()
    {
        $title = 'new Title';
        $title2 = 'new Title2';

        $demo = new Demo();
        $demo->id = 100;
        $demo->language_id = 1;

        $demo->titles = [];
        $demo->titles[] = $title;
        $demo->titles[] = $title2;

        $this->assertEquals(1, $demo->language_id);
        $this->assertEquals(100, $demo->id);
        $this->assertEquals($title, $demo->titles[0]);
        $this->assertEquals($title2, $demo->titles[1]);

        $demo2 = $demo->newRevision(2);
        $this->assertEquals(2, $demo2->language_id);
        $this->assertEquals(100, $demo2->id);
        $this->assertFalse(isset($demo2->titles[0]));
    }
}
