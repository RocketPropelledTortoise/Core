<?php namespace Rocket\Entities\Fields;

use Rocket\Entities\Field;

class String extends Field
{
    /**
     * {@inheritdoc}
     */
    public $table = 'field_string';

    /**
     * {@inheritdoc}
     */
    public static function validateValue($value)
    {
        return is_string($value);
    }
}
