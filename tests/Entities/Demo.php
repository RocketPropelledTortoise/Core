<?php

namespace Rocket\Entities;

/**
 * @property array<string> $titles
 * @property string $title
 */
class Demo extends Entity
{
    public function getFields()
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
