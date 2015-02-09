<?php namespace Rocket\Entities;

use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    public function __construct(array $attributes = array())
    {
        $this->bootIfNotBooted();

        $this->syncOriginal();

        //TODO :: validators
    }

    public function toArray()
    {
        return $this->getAttribute('value');
    }
}
