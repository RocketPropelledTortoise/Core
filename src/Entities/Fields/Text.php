<?php

/**
 * A Text field
 */

namespace Rocket\Entities\Fields;

use Rocket\Entities\Field;

/**
 * Text Field
 *
 * @property string $value The field's value
 */
class Text extends Field
{
    /**
     * @var string The table associated with the model.
     */
    public $table = 'field_text';

    /**
     * Checks if a field is valid
     *
     * @param mixed $value The value to validate
     * @return bool
     */
    protected function isValid($value)
    {
        return is_string($value);
    }
}
