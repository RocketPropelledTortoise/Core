<?php

namespace Rocket\Entities;

class NonExistentType extends Entity
{
    protected function getFields()
    {
        return [
            'content' => [
                'type' => 'no_type', //max width :: 255
            ],
        ];
    }
}
