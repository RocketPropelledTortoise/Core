<?php namespace Rocket\Entities;

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
        $this->assertEquals('demo', Demo::getContentType());
    }

    public function testExtendedGetContentType()
    {
        $this->assertEquals('comment_demo', CommentDemo::getContentType());
    }

    public function testSaveEntity()
    {
        $language_id = Language::value('id');
        $title1 = 'one title';
        $title2 = 'two titles';

        $demo = new Demo($language_id);
        $demo->titles[] = $title1;
        $demo->titles[] = $title2;

        $array = $demo->toArray();
        $this->assertEquals([], $array['_content']);
        $this->assertEquals(['language_id' => $language_id], $array['_revision']);

        $demo->save();

        $array = $demo->toArray();
        $revision_id = $array['_revision']['id'];

        $this->assertInternalType("int", $array['_content']['id']);
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

    public function testRetrieveEntity()
    {
        $language_id = Language::value('id');

        $demo = new Demo($language_id);
        $demo->titles[] = 'one title';
        $demo->titles[] = 'two titles';
        $demo->save();

        $array = $demo->toArray();

        $demo2 = Demo::find($demo->id, $language_id);

        $this->assertEquals($array, $demo2->toArray());
    }

    public function testUpdateEntity()
    {
        $language_id = Language::value('id');

        $demo = new Demo($language_id);
        $demo->titles[] = 'one title';
        $demo->titles[] = 'two titles';
        $demo->save();

        $array = $demo->toArray();

        $demo2 = Demo::find($demo->id, $language_id);

        $this->assertEquals($array, $demo2->toArray());
    }

    public function testCreateNewRevision()
    {
        $this->markTestSkipped("Not implemented yet");
    }

    public function testPublishOldRevision()
    {
        $this->markTestSkipped("Not implemented yet");
    }

    public function testUnpublishContent()
    {
        $this->markTestSkipped("Not implemented yet");
    }

    public function testDeleteRevision()
    {
        $this->markTestSkipped("Not implemented yet");
    }

    public function testDeleteContent()
    {
        $this->markTestSkipped("Not implemented yet");
    }
}
