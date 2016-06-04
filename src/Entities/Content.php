<?php namespace Rocket\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Represent a Content
 *
 * @property int $id The field id
 * @property-read \DateTime $created_at
 * @property-read \DateTime $updated_at
 */
class Content extends Model
{
    public $table = 'contents';

    /**
     * Get the revisions for this class
     */
    public function revisions()
    {
        return $this->hasMany(Revision::class);
    }
}
