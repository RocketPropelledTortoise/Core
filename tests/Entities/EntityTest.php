<?php

namespace Rocket\Entities;

use Rocket\Translation\Model\Language;

class CommentDemo extends Entity
{
}

class Demo extends Entity
{
    protected $fields = [
        'title' => [
            'type' => 'string', //max width :: 255
        ],
        'titles' => [
            'type' => 'string',
            'max_items' => 4
        ]
    ];
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
        return ['\Rocket\Entities\EntitiesServiceProvider'];
    }

    public function testGetContentType()
    {
        $comment = new Demo(['language_id' => 1]);
        $this->assertEquals('demo', $comment->getContentType());
    }

    public function testExtendedGetContentType()
    {
        $comment = new CommentDemo(['language_id' => 1]);
        $this->assertEquals('comment_demo', $comment->getContentType());
    }

    public function testCreateSimpleEntity()
    {
        $first_lang = Language::pluck('id');

        $demo = new Demo(['language_id' => $first_lang]);

        $this->assertEquals($first_lang, $demo->language_id);
    }

    public function testCreateSimpleEntityAndRetrieve()
    {
        $this->markTestSkipped('not ready to continue this right now');
        $title = "new Title";
        $first_lang = Language::pluck('id');

        $demo = new Demo(['language_id' => $first_lang]);
        $demo->title = $title;

        print_r($demo);

        $this->assertEquals($title, $demo->title);
    }

    public function testCreateSimpleEntityMultipleValues()
    {
        $this->markTestSkipped('not ready to continue this right now');
        $title = "new Title";
        $title2 = "new Title2";
        $first_lang = Language::pluck('id');

        $demo = new Demo(['language_id' => $first_lang]);

        $demo->titles = [];
        $demo->titles[] = $title;
        $demo->titles[] = $title;

        $this->assertEquals($title, $demo->titles[0]);
        $this->assertEquals($title2, $demo->titles[1]);
    }

    /*public function testInsertSimpleContent()
    {
        $title = "new Title";
        $first_lang = Language::pluck('id');

        $demo = new Demo(['language_id' => $first_lang]);

        $demo->title = $title;

        $demo->save();

        $demo_get = Demo::find($demo->id, $first_lang);

        $this->assertEquals($title, $demo_get->title);
    }*/
}
