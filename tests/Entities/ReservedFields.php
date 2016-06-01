<?php

namespace Rocket\Entities;

class ReservedFields extends Entity
{
    protected function getFields()
    {
        return [
            'created_at' => [
                'type' => 'string', //max width :: 255
            ],
            'language_id' => [
                'type' => 'string',
                'max_items' => 4,
            ],
        ];
    }
}
