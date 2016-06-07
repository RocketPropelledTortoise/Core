<?php namespace Rocket\Entities;

use Rocket\Entities\Exceptions\EntityNotFoundException;
use Rocket\Entities\Exceptions\NoRevisionForLanguageException;
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
        $this->assertEquals($demo->revision_id, $demo3->revision_id);
        $this->assertEquals($demo2->revision_id, $demo3->revision_id);
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
        $this->assertNotEquals($demo->revision_id, $demo3->revision_id);
        $this->assertEquals($demo2->revision_id, $demo3->revision_id);
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
        $this->assertEquals($demo->revision_id, $demo3->revision_id);

        // We still have two revisions
        $this->assertEquals(2, $demo3->revisions->count());

        // Demo2 contains the new revision whereas Demo3 contains the first revision
        $this->assertNotEquals($demo2->revision_id, $demo3->revision_id);
    }

    /**
     * @expectedException \Rocket\Entities\Exceptions\RevisionEntityMismatchException
     */
    public function testRetrieveRevisionFromOtherEntity()
    {
        $language_id = Language::value('id');

        // GIVEN two entities
        $demo1 = new Demo($language_id);
        $demo1->titles[] = 'one title';
        $demo1->titles[] = 'two titles';
        $demo1->save();

        $demo2 = new Demo($language_id);
        $demo2->titles[] = 'one title';
        $demo2->titles[] = 'two titles';
        $demo2->save();

        // WHEN you load a revision from another content
        Demo::find($demo1->id, $language_id, $demo2->revision_id);
    }

    /**
     * @expectedException \Rocket\Entities\Exceptions\RevisionNotFoundException
     */
    public function testRetrieveNonExistentRevision()
    {
        $language_id = Language::value('id');

        // GIVEN two entities
        $demo1 = new Demo($language_id);
        $demo1->titles[] = 'one title';
        $demo1->titles[] = 'two titles';
        $demo1->save();

        // WHEN you load a revision that doesn't exist
        Demo::find($demo1->id, $language_id, 1000);
    }

    public function testRetrieveOldRevisionAfterNewOneWasCreated()
    {
        $language_id = Language::value('id');

        // GIVEN a base entity
        $demoR1 = new Demo($language_id);
        $demoR1->titles[] = 'one title';
        $demoR1->titles[] = 'two titles';
        $demoR1->save();

        // WHEN an entity is modified
        $demoR2 = Demo::find($demoR1->id, $language_id);
        $demoR2->titles[1] = 'new title';
        unset($demoR2->titles[0]);
        $demoR2->save(true);

        $this->assertCount(0, $demoR2->getField('titles')->deleted());

        // THEN the fields are updated and deleted
        $demoR2Bis = Demo::find($demoR1->id, $language_id);
        $this->assertEquals($demoR2->toArray()['titles'], $demoR2Bis->toArray()['titles']);
        $this->assertNotEquals($demoR1->revision_id, $demoR2Bis->revision_id);
        $this->assertEquals($demoR2->revision_id, $demoR2Bis->revision_id);

        $this->assertEquals(2, $demoR2Bis->revisions->count());

        // WHEN we retrieve the previous revision again
        $demoR1Bis = Demo::find($demoR1->id, $language_id, $demoR1->revision_id);

        // THEN the fields are the same as before
        $this->assertEquals($demoR1->revision_id, $demoR1Bis->revision_id);
        $this->assertNotEquals($demoR1Bis->toArray()['titles'], $demoR2Bis->toArray()['titles']);
        $this->assertEquals($demoR1->toArray()['titles'], $demoR1Bis->toArray()['titles']);
    }

    public function testPublishOldRevision()
    {
        // TODO :: implement feature
        $this->markTestSkipped('Not implemented yet');
    }

    public function testPublishOtherRevision()
    {
        // TODO :: implement feature
        $this->markTestSkipped('Not implemented yet');
    }

    public function testUnpublishContent()
    {
        // TODO :: implement feature
        $this->markTestSkipped('Not implemented yet');
    }

    public function testDeleteRevisionCleared()
    {
        $language_id = Language::value('id');

        // GIVEN a base entity
        $demo = new Demo($language_id);
        $demo->titles[] = 'one title';
        $demo->titles[] = 'two titles';
        $demo->save(true, false);

        $demo->deleteRevision();

        $this->assertInternalType('integer', $demo->id);
        $this->assertCount(0, $demo->titles);

        // WHEN we get this entity
        $threw = false;
        try {
            Demo::find($demo->id, $language_id);
        } catch (NoRevisionForLanguageException $e) {
            $threw = true;
        }

        $this->assertTrue($threw);
    }

    public function testDeleteRevisionUnCleared()
    {
        $language_id = Language::value('id');

        // GIVEN a base entity
        $demo = new Demo($language_id);
        $demo->titles[] = 'one title';
        $demo->titles[] = 'two titles';
        $demo->save(true, false);

        $demo->deleteRevision(false);

        $this->assertInternalType('integer', $demo->id);
        $this->assertCount(2, $demo->titles);

        // WHEN we get this entity
        $threw = false;
        try {
            Demo::find($demo->id, $language_id);
        } catch (NoRevisionForLanguageException $e) {
            $threw = true;
        }

        $this->assertTrue($threw);
    }

    public function testDeleteContentCleared()
    {
        $language_id = Language::value('id');

        // GIVEN a base entity
        $demo = new Demo($language_id);
        $demo->titles[] = 'one title';
        $demo->titles[] = 'two titles';
        $demo->save(true, false);

        $id = $demo->id;
        $demo->delete();

        $this->assertNull($demo->id);
        $this->assertCount(0, $demo->titles);

        // WHEN we get this entity
        $threw = false;
        try {
            Demo::find($id, $language_id);
        } catch (EntityNotFoundException $e) {
            $threw = true;
        }

        $this->assertTrue($threw);
    }

    public function testDeleteContentUncleared()
    {
        $language_id = Language::value('id');

        // GIVEN a base entity
        $demo = new Demo($language_id);
        $demo->titles[] = 'one title';
        $demo->titles[] = 'two titles';
        $demo->save(true, false);

        $id = $demo->id;
        $demo->delete(false);

        $this->assertInternalType('integer', $demo->id);
        $this->assertCount(2, $demo->titles);

        // WHEN we get this entity
        $threw = false;
        try {
            Demo::find($id, $language_id);
        } catch (EntityNotFoundException $e) {
            $threw = true;
        }

        $this->assertTrue($threw);
    }

    /**
     * @expectedException \Rocket\Entities\Exceptions\EntityNotFoundException
     */
    public function testContentNotFound()
    {
        Demo::find(1, Language::value('id'));
    }

    /**
     * @expectedException \Rocket\Entities\Exceptions\NoPublishedRevisionForLanguageException
     */
    public function testNoPublishedRevisionForThisLanguage()
    {
        $language_id = Language::value('id');

        // GIVEN a base entity
        $demo = new Demo($language_id);
        $demo->titles[] = 'one title';
        $demo->titles[] = 'two titles';
        $demo->save(true, false);

        // WHEN we get an this entity but with another language
        Demo::find($demo->id, $language_id);
    }

    /**
     * @expectedException \Rocket\Entities\Exceptions\NoRevisionForLanguageException
     */
    public function testNoRevisionForThisLanguage()
    {
        $language_ids = Language::pluck('id');

        // GIVEN a base entity
        $demo = new Demo($language_ids[0]);
        $demo->titles[] = 'one title';
        $demo->titles[] = 'two titles';
        $demo->save();

        // WHEN we get an this entity but with another language
        Demo::find($demo->id, $language_ids[1]);
    }
}
