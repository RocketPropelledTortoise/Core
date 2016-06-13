<?php

/**
 * A String field
 */
namespace Rocket\Entities\Fields;

use Rocket\Entities\Field;

/**
 * String Field
 *
 * @property string $value The field's value
 */
class StringField extends Field
{
    /**
     * @var string The table associated with the model.
     */
    public $table = 'field_string';

    /**
     * Checks if a field is valid
     *
     * @param mixed $value The value to validate
     * @return bool
     */
    protected function isValid($value)
    {
        return is_string($value) && strlen($value) <= 255;
    }
}
