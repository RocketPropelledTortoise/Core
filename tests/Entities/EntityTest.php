<?php namespace Rocket\Entities;

use Rocket\Entities\Fixtures\Demo;
use Rocket\Entities\Fixtures\NonExistentType;
use Rocket\Entities\Fixtures\ReservedFields;
use Rocket\Entities\Support\Laravel5\Facade as Entities;
use Rocket\Translation\Model\Language;

class EntityTest extends \Rocket\Utilities\DBTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Language::insert(['name' => 'Français', 'iso' => 'fr']);
        Language::insert(['name' => 'English', 'iso' => 'en']);
    }

    protected function getPackageProviders($app)
    {
        return [
            '\Rocket\Translation\Support\Laravel5\ServiceProvider',
            '\Rocket\Entities\Support\Laravel5\ServiceProvider'
        ];
    }

    public function testCreateSimpleEntity()
    {
        $first_lang = Language::value('id');

        $demo = new Demo($first_lang);

        $this->assertEquals($first_lang, $demo->language_id);
    }

    public function testCreateSimpleEntityWithNumberInString()
    {
        $first_lang = Language::value('id');

        $demo = new Demo("$first_lang");

        $this->assertEquals($first_lang, $demo->language_id);
    }

    public function testCreateSimpleEntityAndRetrieve()
    {
        $title = 'new Title';
        $first_lang = Language::value('id');

        $demo = new Demo($first_lang);
        $demo->title = $title;

        $this->assertEquals($title, $demo->title);
    }

    public function testAssignOnMultipleField()
    {
        $this->expectException(\Rocket\Entities\Exceptions\MultipleFieldAssignmentException::class);
        $first_lang = Language::value('id');

        $demo = new Demo($first_lang);
        $demo->titles = 'This is a wrong value';
    }

    public function testFieldCollectionResilience()
    {
        $demo = new Demo(Language::value('id'));

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
        $first_lang = Language::value('id');

        $demo = new Demo($first_lang);

        $demo->titles = [];
        $demo->titles[] = $title;
        $demo->titles[] = $title2;

        $this->assertEquals($title, $demo->titles[0]);
        $this->assertEquals($title2, $demo->titles[1]);
    }

    public function testGetNonExistentField()
    {
        $this->expectException(\Rocket\Entities\Exceptions\NonExistentFieldException::class);
        $demo = new Demo(Language::value('id'));
        $demo->foo = [];
    }

    public function testSetNonExistentField()
    {
        $this->expectException(\Rocket\Entities\Exceptions\NonExistentFieldException::class);
        $demo = new Demo(Language::value('id'));
        $foo = $demo->foo;
    }

    public function testReservedFieldNames()
    {
        $this->expectException(\Rocket\Entities\Exceptions\ReservedFieldNameException::class);
        $test = new ReservedFields(Language::value('id'));
    }

    public function testValidLanguageNeeded()
    {
        $this->expectException(\InvalidArgumentException::class);
        $test = new Demo(0);
    }

    public function testNonExistentFieldType()
    {
        $this->expectException(\Rocket\Entities\Exceptions\InvalidFieldTypeException::class);
        $test = new NonExistentType(Language::value('id'));
    }

    public function testIsset()
    {
        $demo = new Demo(Language::value('id'));

        $demo->titles[] = 'one title';

        $this->assertTrue(isset($demo->titles[0]));
        $this->assertFalse(isset($demo->titles[4]));
    }

    public function testUnset()
    {
        $demo = new Demo(Language::value('id'));

        $demo->titles[] = 'one title';
        $demo->titles[] = 'two titles';

        unset($demo->titles[0]);

        $this->assertEquals(['two titles'], array_values($demo->titles->toArray()));
    }

    public function testCreateRevision()
    {
        $first_lang = Language::value('id');
        $title = 'new Title';
        $title2 = 'new Title2';

        $demo = new Demo($first_lang);
        $demo->id = 100;

        $demo->titles = [];
        $demo->titles[] = $title;
        $demo->titles[] = $title2;

        $this->assertEquals($first_lang, $demo->language_id);
        $this->assertEquals(100, $demo->id);
        $this->assertEquals($title, $demo->titles[0]);
        $this->assertEquals($title2, $demo->titles[1]);

        $demo2 = $demo->newRevision(2);
        $this->assertEquals(2, $demo2->language_id);
        $this->assertEquals(100, $demo2->id);
        $this->assertFalse(isset($demo2->titles[0]));
    }

    public function testGetType()
    {
        $demo = new Demo(Language::value('id'));
        $this->assertEquals('demo', $demo->type);
    }

    public function testReplaceFieldContent()
    {
        $content = ['title', 'title 2'];

        $demo = new Demo(Language::value('id'));
        $demo->titles = $content;

        $this->assertInstanceOf(FieldCollection::class, $demo->getField('titles'));
        $this->assertEquals($content, $demo->toArray()['titles']);
    }
}
