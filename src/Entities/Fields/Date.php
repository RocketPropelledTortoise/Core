<?php

/**
 * A Date field
 */
namespace Rocket\Entities\Fields;

use Rocket\Entities\Field;

/**
 * Date Field
 *
 * @property Date $value The field's value
 */
class Date extends Field
{
    /**
     * @var string The table associated with the model.
     */
    public $table = 'field_date';

    /**
     * @var array The attributes that should be cast to native types.
     */
    protected $casts = [
        'value' => 'date',
    ];

    /**
     * Prepare the value to be stored.
     *
     * @param mixed $value The value to prepare
     * @return string
     */
    protected function prepareValue($value)
    {
        $value = $this->asDateTime($value)->startOfDay();

        return $value->format($this->getDateFormat());
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
        } catch (\Exception $e) {
            return false;
        }
    }
}
