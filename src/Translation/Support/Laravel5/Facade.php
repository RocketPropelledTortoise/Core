<?php namespace Rocket\Translation\Support\Laravel5;

/**
 * Class I18N
 */
class Facade extends \Illuminate\Support\Facades\Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'i18n';
    }
}
