<?php

/**
 * A Double field
 */

namespace Rocket\Entities\Fields;

use Rocket\Entities\Field;

/**
 * A Double field
 *
 * @property double $value The value to store
 */
class Double extends Field
{
    /**
     * @var string The table associated with the model.
     */
    public $table = 'field_double';

    /**
     * Checks if a field is valid
     *
     * @param mixed $value The value to validate
     * @return bool
     */
    protected function isValid($value)
    {
        return is_numeric($value);
    }
}
