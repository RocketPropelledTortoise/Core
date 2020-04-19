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
     * @var array The attributes that are mass assignable.
     */
    protected $fillable = ['term_id', 'language_id', 'title', 'description'];

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (array_key_exists('translated', $attributes)) {
            $this->translated = $attributes['translated'];
        }
    }

    /**
     * Get the term this data is linked to.
     *
     * @codeCoverageIgnore
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function term()
    {
        return $this->belongsTo(TermContainer::class, 'term_id');
    }

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

    public function toArray()
    {
        $array = parent::toArray();
        $array['translated'] = $this->translated;
        return $array;
    }
}
