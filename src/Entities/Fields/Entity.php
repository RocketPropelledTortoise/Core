<?php namespace Rocket\Entities\Fields;

use Rocket\Entities\Field;

class Entity extends Field
{
    /**
     * {@inheritdoc}
     */
    public $table = 'field_entity';

    /**
     * {@inheritdoc}
     */
    public static function validateValue($value)
    {
        return $value instanceof \Rocket\Entities\Entity;
    }

    /**
     * {@inheritdoc}
     */
    public static function getForStorage(Entity $value)
    {
        //TODO :: ensure the entity is already saved

        $field = new static();
        $field->value = $value->id;

        return $field;
    }
}
