<?php

/**
 * Represents the link from a Term to any Model
 */
namespace Rocket\Taxonomy\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Relations from a term to a content
 *
 * @property int $term_id
 * @property int $relationable_id
 * @property string $relationable_type
 */
class TermContent extends Model
{
    /**
     * @var bool Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * @var string The table associated with the model.
     */
    protected $table = 'taxonomy_content';

    /**
     * @var array The attributes that are mass assignable.
     */
    protected $fillable = ['term_id'];

    /**
     * The term linked to this relation.
     *
     * @codeCoverageIgnore
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function term()
    {
        return $this->belongsTo(TermContainer::class, 'term_id');
    }

    /**
     * The model linked to this relation.
     *
     * @codeCoverageIgnore
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function relationable()
    {
        return $this->morphTo();
    }
}
