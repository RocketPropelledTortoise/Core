<?php

/**
 * This class represents the container holding all translations of a Term together.
 */
namespace Rocket\Taxonomy\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * The main term container
 *
 * @property int $id
 * @property int $vocabulary_id
 * @property int $type
 */
class TermContainer extends Model
{
    /**
     * @var bool Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * @var string The table associated with the model.
     */
    protected $table = 'taxonomy_terms';

    /**
     * @var array The attributes that are mass assignable.
     */
    protected $fillable = ['vocabulary_id', 'type'];

    /**
     * All translations available for this term.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany('Rocket\Taxonomy\Model\TermData', 'term_id');
    }

    /**
     * The vocabulary this term is linked to.
     *
     * @codeCoverageIgnore
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vocabulary()
    {
        return $this->belongsTo('Rocket\Taxonomy\Model\Vocabulary', 'vocabulary_id');
    }
}
