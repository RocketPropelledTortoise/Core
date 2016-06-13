<?php

/**
 * A content is the base brick of the Entity system.
 *
 * The Content holds the Revision which in turn holds the Fields.
 */

namespace Rocket\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Represent a Content
 *
 * @property int $id The field id
 * @property bool $published the published state
 * @property string $type the type of the entity
 * @property Revision[] $revisions the revisions attached to this content
 * @property-read \DateTime $created_at
 * @property-read \DateTime $updated_at
 */
class Content extends Model
{
    /**
     * @var array The attributes that should be cast to native types.
     */
    protected $casts = [
        'published' => 'boolean',
    ];

    /**
     * @var array The model's attributes.
     */
    protected $attributes = [
        'published' => true,
    ];

    /**
     * Get the revisions for this class
     *
     * @codeCoverageIgnore
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function revisions()
    {
        return $this->hasMany(Revision::class);
    }
}
