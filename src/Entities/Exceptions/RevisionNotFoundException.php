<?php

/**
 * Represents an Entity Exception
 */

namespace Rocket\Entities\Exceptions;

/**
 * This Exception is thrown if an Entity is loaded with a specific Revision but this revision doesn't exist.
 */
class RevisionNotFoundException extends \Exception
{
}
