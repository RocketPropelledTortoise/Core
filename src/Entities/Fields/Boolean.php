<?php

/**
 * A Boolean field
 */

namespace Rocket\Entities\Fields;

use Rocket\Entities\Field;

/**
 * A Boolean field
 *
 * @property bool $value The value to store
 */
class Boolean extends Field
{
    /**
     * @var string The table associated with the model.
     */
    public $table = 'field_boolean';

    /**
     * @var array The attributes that should be cast to native types.
     */
    protected $casts = [
        'value' => 'boolean',
    ];

    /**
     * Checks if a field is valid
     *
     * @param mixed $value The value to validate
     * @return bool
     */
    protected function isValid($value)
    {
        $acceptable = [true, false, 0, 1, '0', '1'];

        return in_array($value, $acceptable, true);
    }
}
