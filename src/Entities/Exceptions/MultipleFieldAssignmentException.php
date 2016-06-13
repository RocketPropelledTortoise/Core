<?php

/**
 * Represents an Entity Exception
 */
namespace Rocket\Entities\Exceptions;

/**
 * This Exception occurs when you try to assign directly on a field that is an array.
 */
class MultipleFieldAssignmentException extends \Exception
{
}
