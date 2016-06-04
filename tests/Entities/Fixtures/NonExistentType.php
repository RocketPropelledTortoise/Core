<?php namespace Rocket\Entities\Fixtures;

use Rocket\Entities\Entity;

class NonExistentType extends Entity
{
    public function getFields()
    {
        return [
            'content' => [
                'type' => 'no_type', //max width :: 255
            ],
        ];
    }
}
