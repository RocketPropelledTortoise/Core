<?php

/**
 * A vocabulary can contain any number of terms
 */
namespace Rocket\Taxonomy\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * The vocabularies in which you add terms
 *
 * @property int $id
 * @property string $machine_name
 * @property string $description
 * @property int $hierarchy
 * @property bool $translatable
 */
class Vocabulary extends Model
{
    // 0 = disabled, 1 = single, 2 = multiple
    public static $HIERARCHY_FLAT = 0;
    public static $HIERARCHY_SINGLE_PARENT = 1;
    public static $HIERARCHY_MULTIPLE_PARENT = 2;

    /**
     * @var bool Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * @var string The table associated with the model.
     */
    protected $table = 'taxonomy_vocabularies';

    /**
     * @var array The attributes that are mass assignable.
     */
    protected $fillable = ['name', 'machine_name', 'description', 'hierarchy', 'translatable'];

    /**
     * Gives the information if a vocabulary is translatable or not
     *
     * @return bool
     */
    public function isTranslatable()
    {
        return (bool) $this->translatable;
    }
}
