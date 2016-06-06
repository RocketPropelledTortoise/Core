<?php namespace Rocket\Entities;

use Rocket\Entities\Fields\StringField;
use Rocket\Entities\Fixtures\CommentDemo;
use Rocket\Entities\Fixtures\Demo;
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

        // GIVEN a simple entity
        $demo = new Demo($language_id);
        $demo->titles[] = $title1;
        $demo->titles[] = $title2;

        // no id's exist before save
        $array = $demo->toArray();
        $this->assertEquals(['type' => 'demo'], $array['_content']);
        $this->assertEquals(['language_id' => $language_id], $array['_revision']);

        // WHEN this entity is saved
        $demo->save();

        // THEN the id's and creation dates are filled
        $array = $demo->toArray();
        $revision_id = $array['_revision']['id'];

        $this->assertInternalType('int', $array['_content']['id']);
        $this->assertInternalType('int', $revision_id);

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

        // GIVEN a simple entity saved to database
        $demo = new Demo($language_id);
        $demo->title = 'main title';
        $demo->titles[] = 'one title';
        $demo->titles[] = 'two titles';
        $demo->save();

        // WHEN it is retrieved
        $demo2 = Demo::find($demo->id, $language_id);

        // THEN it is identical to the original
        $demoArray = $demo->toArray();
        $demo2Array = $demo2->toArray();

        $this->assertTrue($demo2Array['_content']['published']);
        $this->assertTrue($demo2Array['_revision']['published']);
        $this->assertEquals($demoArray['_content']['id'], $demo2Array['_content']['id']);
        $this->assertEquals($demoArray['_revision']['id'], $demo2Array['_revision']['id']);
        $this->assertEquals($demoArray['title'], $demo2Array['title']);
        $this->assertEquals($demoArray['titles'], $demo2Array['titles']);
    }

    public function testUpdateEntity()
    {
        $language_id = Language::value('id');

        // GIVEN a base entity
        $demo = new Demo($language_id);
        $demo->titles[] = 'one title';
        $demo->titles[] = 'two titles';
        $demo->save();

        // WHEN an entity is modified
        $demo2 = Demo::find($demo->id, $language_id);
        $demo2->titles[1] = 'new title';
        unset($demo2->titles[0]);
        $demo2->save();

        $this->assertCount(0, $demo2->getField('titles')->deleted());

        // THEN the fields are updated and deleted
        $demo3 = Demo::find($demo->id, $language_id);
        $this->assertEquals($demo2->toArray(), $demo3->toArray());
        $this->assertEquals($demo->toArray()['_revision']['id'], $demo3->toArray()['_revision']['id']);
        $this->assertEquals($demo2->toArray()['_revision']['id'], $demo3->toArray()['_revision']['id']);
        $this->assertEquals(1, $demo3->revisions->count());
    }

    public function testCreateNewRevision()
    {
        $language_id = Language::value('id');

        // GIVEN a base entity
        $demo = new Demo($language_id);
        $demo->titles[] = 'one title';
        $demo->titles[] = 'two titles';
        $demo->save();

        // WHEN an entity is modified
        $demo2 = Demo::find($demo->id, $language_id);
        $demo2->titles[1] = 'new title';
        unset($demo2->titles[0]);
        $demo2->save(true);

        // THEN the fields are updated and deleted
        $demo3 = Demo::find($demo->id, $language_id);

        $this->assertEquals($demo2->toArray()['titles'], $demo3->toArray()['titles']);
        $this->assertNotEquals($demo->toArray()['_revision']['id'], $demo3->toArray()['_revision']['id']);
        $this->assertEquals($demo2->toArray()['_revision']['id'], $demo3->toArray()['_revision']['id']);
        $this->assertEquals(2, $demo3->revisions->count());
    }

    public function testCreateUnpublishedRevision()
    {
        $language_id = Language::value('id');

        // GIVEN a base entity
        $demo = new Demo($language_id);
        $demo->titles[] = 'one title';
        $demo->titles[] = 'two titles';
        $demo->save();

        // WHEN an entity is modified and saved but not published
        $demo2 = Demo::find($demo->id, $language_id);
        $demo2->titles[1] = 'new title';
        unset($demo2->titles[0]);
        $demo2->save(true, false);

        $this->assertFalse($demo2->toArray()['_revision']['published']);

        // THEN we get the previous revision
        $demo3 = Demo::find($demo->id, $language_id);

        // Demo and demo3 are identical
        $this->assertEquals($demo->toArray()['titles'], $demo3->toArray()['titles']);
        $this->assertEquals($demo->toArray()['_revision']['id'], $demo3->toArray()['_revision']['id']);

        // We still have two revisions
        $this->assertEquals(2, $demo3->revisions->count());

        // Demo2 contains the new revision whereas Demo3 contains the first revision
        $this->assertNotEquals($demo2->toArray()['_revision']['id'], $demo3->toArray()['_revision']['id']);
    }

    public function testPublishOldRevision()
    {
        // TODO :: implement feature
        $this->markTestSkipped('Not implemented yet');
    }

    public function testUnpublishContent()
    {
        // TODO :: implement feature
        $this->markTestSkipped('Not implemented yet');
    }

    public function testDeleteRevision()
    {
        // TODO :: implement feature
        $this->markTestSkipped('Not implemented yet');
    }

    public function testDeleteContent()
    {
        // TODO :: implement feature
        $this->markTestSkipped('Not implemented yet');
    }
}
