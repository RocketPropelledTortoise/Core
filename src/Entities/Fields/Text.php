<?php namespace Rocket\Entities\Fields;

use Rocket\Entities\Field;

/**
 * Text Field
 *
 * @property string $value The field's value
 */
class Text extends Field
{
    public $table = 'field_text';

    /**
     * {@inheritdoc}
     */
    protected function isValid($value)
    {
        return is_string($value);
    }
}
