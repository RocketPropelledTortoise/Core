<?php namespace Rocket\Entities\Fields;

use Rocket\Entities\Field;

class Date extends Field
{
    public $table = 'field_date';

    public $dates = ['value'];

    /**
     * {@inheritdoc}
     */
    public static function validateValue($value)
    {
        try {
            (new static)->asDateTime($value);
        } catch (\InvalidArgumentException $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public static function sanitizeValue($value)
    {
        return (new static)->asDateTime($value);
    }
}
