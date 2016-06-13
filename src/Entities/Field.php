<?php

/**
 * Base class for a Field Implementation.
 *
 * All Fields must inherit form this Model.
 */

namespace Rocket\Entities;

use Illuminate\Database\Eloquent\Model;
use Rocket\Entities\Exceptions\InvalidValueException;

/**
 * Field
 *
 * @property int $id The field id
 * @property string $name The name of the field
 * @property int $weight The order of this field
 * @property int $revision_id The field's revision id
 * @property-read \DateTime $created_at
 * @property-read \DateTime $updated_at
 */
abstract class Field extends Model
{
    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->getAttribute('value');
    }

    /**
     * Get the revisions for this class
     *
     * @codeCoverageIgnore
     */
    public function revision()
    {
        return $this->belongsTo(Revision::class);
    }

    /**
     * Validate and set the value
     *
     * @param  mixed  $value
     */
    public function setValueAttribute($value)
    {
        if (!$this->isValid($value)) {
            throw new InvalidValueException("The value in the field '" . get_class($this) . "' is invalid");
        }

        $this->attributes['value'] = $this->prepareValue($value);
    }

    /**
     * Prepare the value to be stored.
     *
     * Particularly useful for dates or very special fields
     *
     * @param mixed $value The value to prepare
     * @return mixed
     */
    protected function prepareValue($value)
    {
        return $value;
    }

    /**
     * Checks if a field is valid
     *
     * @param mixed $value The value to validate
     * @return bool
     */
    abstract protected function isValid($value);
}
