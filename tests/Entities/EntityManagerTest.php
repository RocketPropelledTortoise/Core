<?php namespace Rocket\Entities;

use Rocket\Entities\Support\Laravel5\Facade as Entities;
use Rocket\Translation\Model\Language;

class EntityManagerTest extends \Rocket\Utilities\TestCase
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
        $comment = new Demo(Language::value('id'));
        $this->assertEquals('demo', Entities::getContentType($comment));
    }

    public function testExtendedGetContentType()
    {
        $comment = new CommentDemo(Language::value('id'));
        $this->assertEquals('comment_demo', Entities::getContentType($comment));
    }

    public function testSaveEntity()
    {
        $title1 = 'one title';
        $title2 = 'two titles';

        $demo = new Demo(Language::value('id'));
        $demo->titles[] = $title1;
        $demo->titles[] = $title2;

        $this->assertNull($demo->getContent()->id);
        $this->assertNull($demo->getRevision()->id);

        Entities::save($demo);

        $revision_id = $demo->getRevision()->id;

        $this->assertInternalType("int", $demo->getContent()->id);
        $this->assertInternalType("int", $revision_id);

        $titles = $demo->getField('titles')->all();

        $this->assertEquals($title1, $titles[0]->value);
        $this->assertEquals($revision_id, $titles[0]->revision_id);
        $this->assertNotNull($titles[0]->id);
        $this->assertNotNull($titles[0]->created_at);
        $this->assertNotNull($titles[0]->updated_at);

        $this->assertEquals($title2, $titles[1]->value);
        $this->assertEquals($revision_id, $titles[1]->revision_id);
    }
}
