<?php namespace Rocket\Entities\Fields;

use Rocket\Entities\Field;

class Double extends Field
{
    public $table = 'field_double';

    /**
     * {@inheritdoc}
     */
    protected function isValid($value)
    {
        return is_numeric($value);
    }
}
