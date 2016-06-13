<?php

/**
 * Represents an Entity Exception
 */

namespace Rocket\Entities\Exceptions;

/**
 * This Exception occurs when one tries to create a field that is a reserved name of the entity system
 */
class ReservedFieldNameException extends \Exception
{
}
