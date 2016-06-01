<?php

namespace Rocket\Entities;

class Demo extends Entity
{
    protected function getFields()
    {
        return [

            'title' => [
                'type' => 'string', //max width :: 255
            ],
            'titles' => [
                'type' => 'string',
                'max_items' => 4,
            ],
        ];
    }
}
