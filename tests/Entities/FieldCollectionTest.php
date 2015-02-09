<?php

namespace Rocket\Entities;

use Rocket\Entities\Fields\String;

class FieldCollectionTest extends \Rocket\Utilities\TestCase
{
    public function testInit()
    {
        $collection = FieldCollection::initField('\Rocket\Entities\Fields\String', ['max_items' => 4]);

        $this->assertEquals(4, $collection->getMaxItems());
    }

    public function testUnique()
    {
        $collection = FieldCollection::initField('\Rocket\Entities\Fields\String');
        $collection[0] = 'test3';

        $this->assertEquals('test3', $collection->toArray());
    }

    public function testSetFieldInstance()
    {
        $str = new String();
        $str->setAttribute('value', 'test3');

        $collection = FieldCollection::initField('\Rocket\Entities\Fields\String');
        $collection[0] = $str;

        $this->assertEquals('test3', $collection->toArray());
    }

    public function testReplaceFieldInstance()
    {
        $str = new String();
        $str->setAttribute('value', 'test3');

        $collection = FieldCollection::initField('\Rocket\Entities\Fields\String');
        $collection[] = 'hey hey';
        $collection[0] = $str;

        $this->assertEquals('test3', $collection->toArray());
    }

    public function testUniqueEmpty()
    {
        $collection = FieldCollection::initField('\Rocket\Entities\Fields\String');

        $this->assertNull($collection->toArray());
    }

    public function testMultipleValues()
    {
        $collection = FieldCollection::initField('\Rocket\Entities\Fields\String', ['max_items' => 3]);

        $collection[] = 'test';
        $collection[] = 'test2';
        $this->assertEquals(2, $collection->count());

        $collection[1] = 'test3';
        $this->assertEquals(['test', 'test3'], $collection->toArray());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testTooManyItems()
    {
        $collection = FieldCollection::initField('\Rocket\Entities\Fields\String', ['max_items' => 2]);

        $collection[] = 'test';
        $collection[] = 'test2';
        $collection[] = 'test3';
    }

    public function testAddItems()
    {
        $collection = FieldCollection::initField('\Rocket\Entities\Fields\String', ['max_items' => 2]);

        $collection[] = 'test';
        $collection[] = 'test2';
        $collection[1] = 'test3';

        $this->assertEquals(2, $collection->getMaxItems());
    }
}
