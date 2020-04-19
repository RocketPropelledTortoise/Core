<?php namespace Rocket\Translation\Model;

use Carbon\Carbon;
use Eloquent;

/**
 * @property integer $id
 * @property DateTime $date_creation
 * @property string $context
 * @property string $string
 */
class StringModel extends Eloquent
{
    /**
     * @var bool Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * @var string The table associated with the model.
     */
    protected $table = 'strings';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['date_creation'];

    public static function getStringId($context, $keyString)
    {
        return StringModel::where('string', $keyString)->where('context', $context)->value('id');
    }

    public static function getOrCreateTranslation($context, $keyString, $languageId, $defaultLanguageId)
    {
        $stringId = static::getStringId($context, $keyString);
        if ($stringId) {
            return Translation::getTranslation($stringId, $languageId);
        }

        static::createString($context, $keyString, $defaultLanguageId);

        return null;
    }

    protected static function createString($context, $text, $defaultLanguageId)
    {
        $string = new StringModel();
        $string->date_creation = Carbon::now();
        $string->context = $context;
        $string->string = $text;
        $string->save();

        // Insert the app's default language id as default translation;
        return Translation::setTranslation($string->id, $defaultLanguageId, $text);
    }
}
