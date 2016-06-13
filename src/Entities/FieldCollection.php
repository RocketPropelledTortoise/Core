<?php
/**
 * A FieldCollection is a Collection that wraps all values in their Field models.
 *
 * It will also return the actual value but not the Field model.
 */

namespace Rocket\Entities;

use Illuminate\Support\Collection;
use Rocket\Entities\Exceptions\InvalidFieldTypeException;
use Rocket\Entities\Exceptions\ItemCountException;
use Rocket\Entities\Exceptions\NullValueException;

/**
 * Smart Collection for entities
 */
class FieldCollection extends \Illuminate\Support\Collection
{
    /**
     * @var int The max items this collection can hold
     */
    protected $maxItems = 1;

    /**
     * @var array The Collection configuration
     */
    protected $configuration = [];

    /**
     * @var string The type of this collection
     */
    protected $type;

    /**
     * @var array An array of fields that were deleted
     */
    protected $deleted = [];

    /**
     * Initialize a collection with the configuration
     *
     * @param array $configuration
     * @throws InvalidFieldTypeException
     * @return static
     */
    public static function initField($configuration = [])
    {
        if (!array_key_exists('type', $configuration) || !class_exists($configuration['type'])) {
            throw new InvalidFieldTypeException('You did not specify a type on this class.');
        }

        $collection = new static();
        $collection->configuration = $configuration;
        $collection->type = $configuration['type'];

        if (array_key_exists('max_items', $configuration)) {
            $collection->maxItems = $configuration['max_items'];
        }

        return $collection;
    }

    /**
     * Validate input of OffsetSet
     *
     * @param $key
     * @param $value
     * @throws ItemCountException
     * @throws NullValueException
     */
    protected function validateSet($key, $value)
    {
        $maxItems = $this->getMaxItems();
        if ((is_null($key) || !array_key_exists($key, $this->items)) && $maxItems != 0 && $this->count() >= $maxItems) {
            throw new ItemCountException('The maximum number of items has been reached on this field.');
        }

        if (is_null($key) && is_null($value)) {
            throw new NullValueException('You cannot add a null value');
        }
    }

    /**
     * Get a field from a value
     *
     * @param Field|mixed $value
     * @return Field
     */
    protected function getFieldInstance($value)
    {
        if ($value instanceof Field) {
            return $value;
        }

        $container = new $this->type();
        $container->value = $value;

        return $container;
    }

    /**
     * Set the item at a given offset.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->validateSet($key, $value);

        if (is_null($value)) {
            $this->offsetUnset($key);

            return;
        }

        if (is_null($key)) {
            $this->items[] = $this->getFieldInstance($value);

            return;
        }

        if ($value instanceof Field && $this->has($key)) {
            $this->deleted[] = $this->items[$key];
        }

        $this->items[$key] = $this->getFieldInstance($value);
    }

    /**
     * Get an item at a given offset.
     *
     * @param  mixed  $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return parent::offsetGet($key)->value;
    }

    /**
     * Unset the item at a given offset.
     *
     * @param  string  $key
     * @return void
     */
    public function offsetUnset($key)
    {
        if ($this->offsetExists($key)) {
            $this->deleted[] = $this->items[$key];
        }
        parent::offsetUnset($key);
    }

    /**
     * Get all deleted fields
     *
     * @return Collection
     */
    public function deleted()
    {
        return new Collection(array_diff($this->deleted, $this->items));
    }

    /**
     * Mark the content as current and saved
     */
    public function syncOriginal()
    {
        $this->deleted = [];
    }

    /**
     * Remove all items in this collection
     *
     * @return void
     */
    public function clear()
    {
        foreach ($this->items as $item) {
            $this->deleted[] = $item;
        }

        $this->items = [];
    }

    /**
     * Get the number of items possible in this collection.
     *
     * @return int
     */
    public function getMaxItems()
    {
        return $this->maxItems;
    }

    /**
     * As we use a field collection even if we have only one value, we use it that way.
     *
     * @return array|mixed|null
     */
    public function toArray()
    {
        if ($this->getMaxItems() != 1) {
            return parent::toArray();
        }

        if (!array_key_exists(0, $this->items)) {
            return null;
        }

        return $this->get(0)->toArray();
    }

    /**
     * Convert the collection to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->getMaxItems() == 1) {
            return $this->items[0]->value;
        }

        return 'Array';
    }
}
