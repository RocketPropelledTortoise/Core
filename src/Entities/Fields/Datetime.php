<?php namespace Rocket\Entities\Fields;

use Rocket\Entities\Field;

/**
 * DateTime Field
 *
 * @property DateTime $value The field's value
 */
class Datetime extends Field
{
    public $table = 'field_datetime';

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'value' => 'datetime',
    ];

    /**
     * {@inheritdoc}
     */
    protected function prepareValue($value)
    {
        return $this->fromDateTime($value);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return $this->serializeDate($this->getAttribute('value'));
    }

    /**
     * {@inheritdoc}
     */
    protected function isValid($value)
    {
        try {
            $this->asDateTime($value);

            return true;
        } catch (\InvalidArgumentException $e) {
            return false;
        }
    }
}
