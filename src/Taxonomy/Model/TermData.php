<?php

/**
 * This model represents one translation for a term.
 */
namespace Rocket\Taxonomy\Model;

use Illuminate\Database\Eloquent\Model;
use Rocket\Taxonomy\Support\Laravel5\Facade as T;

/**
 * The translation Data for a term
 *
 * @property int $id
 * @property int $term_id
 * @property int $language_id
 * @property string $title
 * @property string $description
 */
class TermData extends Model
{
    /**
     * @var bool Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * @var string The table associated with the model.
     */
    protected $table = 'taxonomy_terms_data';

    /**
     * When used in a term, we set this to true or false
     */
    public $translated = true;

    /**
     * Get the term this data is linked to.
     *
     * @codeCoverageIgnore
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function term()
    {
        return $this->belongsTo('Rocket\Taxonomy\Model\TermContainer', 'term_id');
    }

    /**
     * @var array The attributes that are mass assignable.
     */
    protected $fillable = ['term_id', 'language_id', 'title', 'description'];

    /**
     * Save the model to the database.
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = [])
    {
        if ($this->translated === false) {
            $this->translated = true;
        }

        T::uncacheTerm($this->term_id);

        parent::save($options);
    }
}
