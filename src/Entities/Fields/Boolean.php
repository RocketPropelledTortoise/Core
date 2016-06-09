<?php namespace Rocket\Entities\Fields;

use Rocket\Entities\Field;

class Boolean extends Field
{
    public $table = 'field_boolean';

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'value' => 'boolean',
    ];

    /**
     * {@inheritdoc}
     */
    protected function isValid($value)
    {
        $acceptable = [true, false, 0, 1, '0', '1'];

        return in_array($value, $acceptable, true);
    }
}
