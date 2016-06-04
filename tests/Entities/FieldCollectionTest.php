<?php namespace Rocket\Entities;

use Rocket\Entities\Fields\String;

class FieldCollectionTest extends \Rocket\Utilities\TestCase
{
    protected function getFieldCollection(array $options = [])
    {
        return FieldCollection::initField(array_merge_recursive(['type' => String::class], $options));
    }

    public function testInit()
    {
        $collection = $this->getFieldCollection(['max_items' => 4]);

        $this->assertEquals(4, $collection->getMaxItems());
    }

    public function testUnique()
    {
        $collection = $this->getFieldCollection();
        $collection[0] = 'test3';

        $this->assertEquals('test3', $collection->toArray());
    }

    public function testSetFieldInstance()
    {
        $collection = $this->getFieldCollection();
        $collection[0] = 'test3';

        $this->assertEquals('test3', $collection->toArray());
    }

    public function testReplaceFieldInstance()
    {
        $collection = $this->getFieldCollection();
        $collection[] = 'hey hey';
        $collection[0] = 'test3';

        $this->assertEquals('test3', $collection->toArray());
    }

    public function testUniqueEmpty()
    {
        $collection = $this->getFieldCollection();

        $this->assertNull($collection->toArray());
    }

    public function testMultipleValues()
    {
        $collection = $this->getFieldCollection(['max_items' => 3]);

        $collection[] = 'test';
        $collection[] = 'test2';
        $this->assertEquals(2, $collection->count());

        $collection[1] = 'test3';
        $this->assertEquals(['test', 'test3'], $collection->toArray());
    }

    /**
     * @expectedException \Rocket\Entities\Exceptions\ItemCountException
     */
    public function testTooManyItems()
    {
        $collection = $this->getFieldCollection(['max_items' => 2]);

        $collection[] = 'test';
        $collection[] = 'test2';
        $collection[] = 'test3';
    }

    public function testRemoveItem()
    {
        $collection = $this->getFieldCollection(['max_items' => 3]);

        $collection[] = 'test';
        $collection[] = 'test2';
        $collection[] = 'test3';

        unset($collection[1]);

        $this->assertEquals(['test', 'test3'], array_values($collection->toArray()));
    }

    public function testIssetItem()
    {
        $collection = $this->getFieldCollection(['max_items' => 3]);

        $collection[] = 'test';
        $collection[] = 'test2';
        $collection[] = 'test3';

        $this->assertTrue(isset($collection[1]));
        $this->assertFalse(isset($collection[4]));
    }

    public function testAddItems()
    {
        $collection = $this->getFieldCollection(['max_items' => 2]);

        $collection[] = 'test';
        $collection[] = 'test2';
        $collection[1] = 'test3';

        $this->assertEquals(2, $collection->getMaxItems());
    }

    public function testToString()
    {
        $collection = $this->getFieldCollection(['max_items' => 1]);
        $collection[] = 'test';
        $this->assertEquals('test', strval($collection));

        $collection = $this->getFieldCollection(['max_items' => 2]);
        $collection[] = 'test';
        $collection[] = 'test2';
        $this->assertEquals('Array', strval($collection));
    }
}
