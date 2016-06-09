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


    public function testUnlimitedValues()
    {
        $collection = $this->getFieldCollection(['max_items' => 0]);

        $collection[] = 'test';
        $collection[] = 'test2';
        $collection[] = 'heya';
        $collection[] = 'even more values';
        $collection[] = 'test3';

        $this->assertEquals(['test', 'test2', 'heya', 'even more values', 'test3'], $collection->toArray());
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
        $this->assertInstanceOf(Field::class, $collection->deleted()[0]);
        $this->assertEquals(['test', 'test2'], $collection->deleted()->toArray());
    }

    public function testGetDeletedOnClear()
    {
        $collection = $this->getFieldCollection(['max_items' => 2]);
        $collection[0] = 'test';
        $collection[1] = 'test2';

        $collection->clear();

        $this->assertCount(0, $collection);
        $this->assertInstanceOf(Field::class, $collection->deleted()[0]);
        $this->assertInstanceOf(Field::class, $collection->deleted()[1]);
        $this->assertEquals(['test', 'test2'], $collection->deleted()->toArray());
    }

    public function testDeletedByReplacement()
    {
        $collection = $this->getFieldCollection(['max_items' => 2]);
        $collection[0] = 'test';

        $value1 = new StringField();
        $value1->value = 'test2';
        $collection[0] = $value1;

        $this->assertCount(1, $collection);
        $this->assertInstanceOf(Field::class, $collection->deleted()[0]);
        $this->assertEquals(['test'], $collection->deleted()->toArray());
    }

    public function testGetDeletedTricky()
    {
        // Very weird case, might never happen ... but we can get the fields throug "getField"

        // GIVEN a simple collection
        $collection = $this->getFieldCollection(['max_items' => 2]);
        $collection[0] = 'test';
        $collection[1] = 'test2';

        // WHEN a field is removed and re-added
        $field = $collection->all()[1];
        unset($collection[1]);
        $collection[1] = $field;

        // THEN there should be no field in the "deleted" list
        $this->assertCount(2, $collection);
        $this->assertCount(0, $collection->deleted());
        $this->assertEquals([], $collection->deleted()->toArray());
    }
}
