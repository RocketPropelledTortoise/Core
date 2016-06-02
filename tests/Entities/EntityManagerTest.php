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

}
