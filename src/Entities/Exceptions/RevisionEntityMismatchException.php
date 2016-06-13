<?php

/**
 * Represents an Entity Exception
 */
namespace Rocket\Entities\Exceptions;

/**
 * This Exception occurs when one tries to load an revision for an Entity which belongs to another Entity.
 */
class RevisionEntityMismatchException extends \Exception
{
}
