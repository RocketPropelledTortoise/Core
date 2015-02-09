<?php namespace Rocket\Entities;

class FieldCollection extends \Illuminate\Support\Collection
{
    /**
     * @var string The class name of the childs
     */
    protected $itemClass;

    /**
     * @var int The max items this collection can hold
     */
    protected $maxItems = 1;

    /**
     * @var array The Collection configuration
     */
    protected $configuration = [];

    /**
     * Initialize a collection with the configuration
     *
     * @param $itemClass
     * @param array $configuration
     * @return FieldCollection
     */
    public static function initField($itemClass, $configuration = [])
    {
        $collection = new static();
        $collection->itemClass = $itemClass;
        $collection->configuration = $configuration;

        if (array_key_exists('max_items', $configuration)) {
            $collection->maxItems = $configuration['max_items'];
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($key, $value)
    {
        if ((is_null($key) || !array_key_exists($key, $this->items)) && $this->count() >= $this->getMaxItems()) {
            throw new \RuntimeException('The maximum number of items has been attained on this field.');
        }

        if ($value instanceof $this->itemClass) {
            return parent::offsetSet($key, $value);
        }

        if (is_null($key) || !array_key_exists($key, $this->items)) {
            $item = new $this->itemClass();
            $item->setAttribute('value', $value);

            if (is_null($key)) {
                $this->items[] = $item;
            } else {
                $this->items[$key] = $item;
            }

            return;
        }

        $this->items[$key]->setAttribute('value', $value);
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
     * @return array|null
     */
    public function toArray()
    {
        if ($this->maxItems != 1) {
            return parent::toArray();
        }

        if (!array_key_exists(0, $this->items)) {
            return null;
        }

        return $this->items[0]->toArray();
    }
}
