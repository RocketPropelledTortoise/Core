<?php namespace Rocket\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * A revision is the status of a content at a certain moment in time
 *
 * @property int $id The field id
 * @property int $language_id the language of this revision
 * @property int $content_id the content this revision is related to
 * @property bool $published the published state
 * @property Content $content the content this revision is linked to
 * @property-read \DateTime $created_at
 * @property-read \DateTime $updated_at
 */
class Revision extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'published' => 'boolean',
    ];

    /**
     * {@inheritdoc}
     */
    protected $attributes = [
        'published' => true,
    ];

    /**
     * Get the revisions for this class
     *
     * @codeCoverageIgnore
     */
    public function content()
    {
        return $this->belongsTo(Content::class);
    }
}
