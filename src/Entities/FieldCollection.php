<?php namespace Rocket\Entities;

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
     * Initialize a collection with the configuration
     *
     * @param array $configuration
     * @return static
     */
    public static function initField($configuration = [])
    {
        $collection = new static();
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
            throw new \RuntimeException('The maximum number of items has been reached on this field.');
        }

        if (is_null($key) || !array_key_exists($key, $this->items)) {
            if (is_null($key)) {
                $this->items[] = $value;
            } else {
                $this->items[$key] = $value;
            }

            return;
        }

        $this->items[$key] = $value;
    }

    /**
     * Remove all items in this collection
     *
     * @return void
     */
    public function clear()
    {
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
     * @return array|null
     */
    public function toArray()
    {
        if ($this->maxItems != 1) {
            return parent::toArray();
        }

        if (!array_key_exists(0, $this->items)) {
            return;
        }

        return $this->items[0];
    }

    public function __toString()
    {
        if ($this->maxItems == 1) {
            return $this->items[0];
        }

        return 'Array';
    }
}
