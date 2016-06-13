<?php

/**
 * An Integer field
 */

namespace Rocket\Entities\Fields;

use Rocket\Entities\Field;

/**
 * An Integer field
 *
 * @property int $value The value to store
 */
class Integer extends Field
{
    /**
     * @var string The table associated with the model.
     */
    public $table = 'field_integer';

    /**
     * Checks if a field is valid
     *
     * @param mixed $value The value to validate
     * @return bool
     */
    protected function isValid($value)
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }
}
