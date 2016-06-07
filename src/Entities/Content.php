<?php namespace Rocket\Entities;

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
     * {@inheritdoc}
     */
    protected $casts = [
        'published' => 'boolean',
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
