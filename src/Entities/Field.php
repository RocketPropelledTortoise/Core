<?php namespace Rocket\Entities;

use Illuminate\Database\Eloquent\Model;

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
class Field extends Model
{
    public function __construct(array $attributes = [])
    {
        $this->bootIfNotBooted();

        $this->syncOriginal();
    }

    //TODO :: validate values

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
}
