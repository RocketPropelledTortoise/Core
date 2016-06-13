<?php

/**
 * Represents an Entity Exception
 */
namespace Rocket\Entities\Exceptions;

/**
 * This error is thrown if a requested entity isn't found (throuh Entity::find)
 */
class EntityNotFoundException extends \Exception
{
}
