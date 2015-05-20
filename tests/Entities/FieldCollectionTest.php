<?php namespace Rocket\Entities;

class FieldCollectionTest extends \Rocket\Utilities\TestCase
{
    public function testInit()
    {
        $collection = FieldCollection::initField(['max_items' => 4]);

        $this->assertEquals(4, $collection->getMaxItems());
    }

    public function testUnique()
    {
        $collection = FieldCollection::initField();
        $collection[0] = 'test3';

        $this->assertEquals('test3', $collection->toArray());
    }

    public function testSetFieldInstance()
    {
        $collection = FieldCollection::initField();
        $collection[0] = 'test3';

        $this->assertEquals('test3', $collection->toArray());
    }

    public function testReplaceFieldInstance()
    {
        $collection = FieldCollection::initField();
        $collection[] = 'hey hey';
        $collection[0] = 'test3';

        $this->assertEquals('test3', $collection->toArray());
    }

    public function testUniqueEmpty()
    {
        $collection = FieldCollection::initField();

        $this->assertNull($collection->toArray());
    }

    public function testMultipleValues()
    {
        $collection = FieldCollection::initField(['max_items' => 3]);

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
        $collection = FieldCollection::initField(['max_items' => 2]);

        $collection[] = 'test';
        $collection[] = 'test2';
        $collection[] = 'test3';
    }

    public function testRemoveItem()
    {
        $collection = FieldCollection::initField(['max_items' => 3]);

        $collection[] = 'test';
        $collection[] = 'test2';
        $collection[] = 'test3';

        unset($collection[1]);

        $this->assertEquals(['test', 'test3'], array_values($collection->toArray()));
    }

    public function testIssetItem()
    {
        $collection = FieldCollection::initField(['max_items' => 3]);

        $collection[] = 'test';
        $collection[] = 'test2';
        $collection[] = 'test3';

        $this->assertTrue(isset($collection[1]));
        $this->assertFalse(isset($collection[4]));
    }

    public function testAddItems()
    {
        $collection = FieldCollection::initField(['max_items' => 2]);

        $collection[] = 'test';
        $collection[] = 'test2';
        $collection[1] = 'test3';

        $this->assertEquals(2, $collection->getMaxItems());
    }

    public function testToString()
    {
        $collection = FieldCollection::initField(['max_items' => 1]);
        $collection[] = 'test';
        $this->assertEquals("test", strval($collection));


        $collection = FieldCollection::initField(['max_items' => 2]);
        $collection[] = 'test';
        $collection[] = 'test2';
        $this->assertEquals("Array", strval($collection));
    }
}
