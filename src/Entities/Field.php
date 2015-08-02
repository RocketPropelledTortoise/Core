<?php namespace Rocket\Entities;

use Illuminate\Database\Eloquent\Model;

abstract class Field extends Model
{
    /**
     * Validates a value against the field
     *
     * @param $value
     * @return boolean
     */
    public static function validateValue($value) {
        return true;
    }

    /**
     * Sanitizes a value against the field
     *
     * @param $value
     * @return boolean
     */
    public static function sanitizeValue($value) {
        return $value;
    }

    /**
     * Take a value to be stored in a database
     *
     * @param $value
     * @return static
     */
    public static function getForStorage($value)
    {
        $field = new static();
        $field->value = $value;

        return $field;
    }

    /**
     * Take a value coming from the database to an entity
     *
     * @param $value
     * @return mixed
     */
    public static function getForHydration($value)
    {
        return $value;
    }
}
