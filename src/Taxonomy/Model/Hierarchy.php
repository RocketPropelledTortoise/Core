<?php

/**
 * Represents thelinks between parents and children terms.
 */
namespace Rocket\Taxonomy\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Parent-child relations between terms
 *
 * @property int $term_id
 * @property int $parent_id
 */
class Hierarchy extends Model
{
    /**
     * @var bool Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * @var string The table associated with the model.
     */
    protected $table = 'taxonomy_term_hierarchy';

    /**
     * Get the term related to this entry
     *
     * @codeCoverageIgnore
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function term()
    {
        return $this->belongsTo(TermContainer::class, 'term_id');
    }

    /**
     * Get the parent term of this entry
     *
     * @codeCoverageIgnore
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(TermContainer::class, 'parent_id');
    }
}
