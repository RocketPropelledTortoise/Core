<?php

/**
 * I18N facade
 */

namespace Rocket\Translation;

use Illuminate\Support\Facades\Facade;

/**
 * Class I18N
 */
class I18NFacade extends Facade
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
