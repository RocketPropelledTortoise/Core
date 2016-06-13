<?php

/**
 * A Datetime field
 */
namespace Rocket\Entities\Fields;

use Rocket\Entities\Field;

/**
 * DateTime Field
 *
 * @property DateTime $value The field's value
 */
class Datetime extends Field
{
    /**
     * @var string The table associated with the model.
     */
    public $table = 'field_datetime';

    /**
     * @var array The attributes that should be cast to native types.
     */
    protected $casts = [
        'value' => 'datetime',
    ];

    /**
     * Prepare the value to be stored.
     *
     * @param mixed $value The value to prepare
     * @return string
     */
    protected function prepareValue($value)
    {
        return $this->fromDateTime($value);
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->serializeDate($this->getAttribute('value'));
    }

    /**
     * Checks if a field is valid
     *
     * @param mixed $value The value to validate
     * @return bool
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
