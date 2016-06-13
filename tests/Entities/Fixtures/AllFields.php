<?php namespace Rocket\Entities\Fixtures;

use Rocket\Entities\Entity;

/**
 * @property bool $bool
 * @property bool[] $bools
 * @property Date $date
 * @property Date[] $dates
 * @property \Datetime $datetime
 * @property \Datetime[] $datetimes
 * @property float $double
 * @property float[] $doubles
 * @property int $integer
 * @property int[] $integers
 * @property string $string
 * @property string[] $strings
 * @property string $text
 * @property string[] $texts
 */
class AllFields extends Entity
{
    public function getFields()
    {
        return [
            'bool' => [
                'type' => 'bool',
            ],
            'bools' => [
                'type' => 'bool',
                'max_items' => 2,
            ],
            'date' => [
                'type' => 'date',
            ],
            'dates' => [
                'type' => 'date',
                'max_items' => 2,
            ],
            'datetime' => [
                'type' => 'datetime',
            ],
            'datetimes' => [
                'type' => 'datetime',
                'max_items' => 2,
            ],
            'double' => [
                'type' => 'double',
            ],
            'doubles' => [
                'type' => 'number',
                'max_items' => 2,
            ],
            'integer' => [
                'type' => 'integer',
            ],
            'integers' => [
                'type' => 'int',
                'max_items' => 2,
            ],
            'string' => [
                'type' => 'string', //max width :: 255
            ],
            'strings' => [
                'type' => 'string',
                'max_items' => 2,
            ],
            'text' => [
                'type' => 'text',
            ],
            'texts' => [
                'type' => 'text',
                'max_items' => 2,
            ],
        ];
    }
}
