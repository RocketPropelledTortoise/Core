<?php

/**
 * Represents an Entity Exception
 */
namespace Rocket\Entities\Exceptions;

/**
 * This Exception occurs when we request an Entity in a specific language.
 * The Entity exists and has revisions in this language, but none is published.
 */
class NoPublishedRevisionForLanguageException extends \Exception
{
}
