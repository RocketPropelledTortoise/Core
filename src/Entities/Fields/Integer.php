<?php namespace Rocket\Entities\Fields;

use Rocket\Entities\Field;

class Integer extends Field
{
    public $table = 'field_integer';

    /**
     * {@inheritdoc}
     */
    protected function isValid($value)
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }
}
