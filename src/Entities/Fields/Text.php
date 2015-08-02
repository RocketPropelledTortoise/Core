<?php namespace Rocket\Entities\Fields;

use Rocket\Entities\Field;

class Text extends Field
{
    /**
     * {@inheritdoc}
     */
    public $table = 'field_text';

    /**
     * {@inheritdoc}
     */
    public static function validateValue($value)
    {
        return is_string($value);
    }
}
