<?php namespace Rocket\Translation\Model;

use Carbon\Carbon;
use Eloquent;

/**
 * @property integer $id
 * @property integer $string_id
 * @property integer $language_id
 * @property DateTime $date_edition
 * @property string $text
 */
class Translation extends Eloquent
{
    /**
     * @var bool Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * @var string The table associated with the model.
     */
    protected $table = 'translations';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['date_edition'];

    public static function setTranslation($stringId, $languageId, $text) {
        $translation = Translation::where('string_id', $stringId)->where('language_id', $languageId)->first();

        if (!$translation) {
            $translation = new Translation();
            $translation->string_id = $stringId;
            $translation->language_id = $languageId;
        }

        $translation->text = $text;

        if ($translation->isDirty()) {
            $translation->date_edition = Carbon::now();
        }

        $translation->save();

        return $translation;
    }

    protected static function getTranslation($stringId, $languageId)
    {
        return Translation::where('string_id', $stringId)->where('language_id', $languageId)->value('text');
    }
}
