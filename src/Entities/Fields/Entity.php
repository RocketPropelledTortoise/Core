<?php

/**
 * An entity field
 */
namespace Rocket\Entities\Fields;

use Rocket\Entities\Field;

/**
 * Date Field
 *
 * @property Entity $value The field's value
 */
class Entity extends Field
{
    /**
     * @var string The table associated with the model.
     */
    public $table = 'field_entity';

    //TODO :: implement entity
    //TODO :: lazy load and save entities
}
