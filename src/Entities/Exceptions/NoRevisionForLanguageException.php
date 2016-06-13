<?php

/**
 * Represents an Entity Exception
 */

namespace Rocket\Entities\Exceptions;

/**
 * This exception occurs when an Entity is requested in a language and not a single Revision exists in that language.
 */
class NoRevisionForLanguageException extends \Exception
{
}
