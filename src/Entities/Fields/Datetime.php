<?php namespace Rocket\Entities\Fields;

use Rocket\Entities\Field;

/**
 * DateTime Field
 *
 * @property DateTime $value The field's value
 */
class Datetime extends Field
{
    public $table = 'field_datetime';

    //TODO :: treat date fields as such
}
