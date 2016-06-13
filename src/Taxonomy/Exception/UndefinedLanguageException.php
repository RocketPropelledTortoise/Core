<?php

/**
 * A Taxonomy Exception
 */

namespace Rocket\Taxonomy\Exception;

/**
 * This Exception occurs when one tries to edit a term in a language that wasn't created yet.
 */
class UndefinedLanguageException extends \Exception
{
}
