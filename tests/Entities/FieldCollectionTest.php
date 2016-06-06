<?php namespace Rocket\Entities;

use Rocket\Entities\Fields\StringField;

class FieldCollectionTest extends \Rocket\Utilities\TestCase
{
    protected function getFieldCollection(array $options = [])
    {
        return FieldCollection::initField(array_merge_recursive(['type' => StringField::class], $options));
    }

    public function testInit()
    {
        $collection = $this->getFieldCollection(['max_items' => 4]);

        $this->assertEquals(4, $collection->getMaxItems());
    }

    /**
     * @expectedException \Rocket\Entities\Exceptions\InvalidFieldTypeException
     */
    public function testInvalidFieldType()
    {
        $collection = FieldCollection::initField();
    }

    /**
     * @expectedException \Rocket\Entities\Exceptions\NullValueException
     */
    public function testAddNullValue()
    {
        $collection = $this->getFieldCollection();
        $collection[] = null;
    }

    public function testUnique()
    {
        $collection = $this->getFieldCollection();
        $collection[0] = 'test3';

        $this->assertEquals('test3', $collection->toArray());
    }

    public function testSetFieldValue()
    {
        $collection = $this->getFieldCollection();
        $collection[0] = 'test3';

        $this->assertEquals('test3', $collection->toArray());
    }

    public function testReplaceFieldValue()
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

    public function testRemoveItemByNull()
    {
        $collection = $this->getFieldCollection(['max_items' => 3]);

        $collection[] = 'test';
        $collection[] = 'test2';
        $collection[] = 'test3';

        $collection[1] = null;

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

    public function testGetDeleted()
    {
        $collection = $this->getFieldCollection(['max_items' => 2]);
        $collection[0] = 'test';
        $collection[1] = 'test2';
        $this->assertCount(2, $collection);

        unset($collection[0]);
        $collection[1] = null;

        $this->assertCount(0, $collection);
        $this->assertEquals(['test', 'test2'], $collection->deleted()->toArray());
    }

    public function testGetDeletedOnClear()
    {
        // When we clear the collection, all fields must be marked as deleted

        // TODO :: implement feature
        $this->markTestSkipped('Not implemented');
    }

    public function testDeletedByReplacement()
    {
        // If a value is replaced by a Field instance,
        // the other instance is ditched.
        // we must track that deletion

        // TODO :: implement feature
        $this->markTestSkipped('Not implemented');
    }

    public function testGetDeletedTricky()
    {
        // If a value is taken to be placed somewhere else,
        // it might be detected as a deletion, but it actually isn't

        // TODO :: implement feature
        $this->markTestSkipped('Not implemented');
    }
}

