<?php

return [
    'entities' => [],
    'field_types' => [
        'string' => \Rocket\Entities\Fields\StringField::class,
        'text' => \Rocket\Entities\Fields\Text::class,
        'integer' => \Rocket\Entities\Fields\Integer::class,
        'int' => \Rocket\Entities\Fields\Integer::class,
        'double' => \Rocket\Entities\Fields\Double::class,
        'number' => \Rocket\Entities\Fields\Double::class,
        'boolean' => \Rocket\Entities\Fields\Boolean::class,
        'bool' => \Rocket\Entities\Fields\Boolean::class,
        'date' => \Rocket\Entities\Fields\Date::class,
        'datetime' => \Rocket\Entities\Fields\Datetime::class,
        'entity' => \Rocket\Entities\Fields\Entity::class,
    ],
];
