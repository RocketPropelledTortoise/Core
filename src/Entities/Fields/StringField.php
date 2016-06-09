<?php namespace Rocket\Entities\Fields;

use Rocket\Entities\Field;

/**
 * Date Field
 *
 * @property string $value The field's value
 */
class StringField extends Field
{
    public $table = 'field_string';

    /**
     * {@inheritdoc}
     */
    protected function isValid($value)
    {
        return is_string($value) && strlen($value) <= 255;
    }
}
