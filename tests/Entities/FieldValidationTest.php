<?php namespace Rocket\Entities;

use Carbon\Carbon;
use Rocket\Entities\Fields\Boolean;
use Rocket\Entities\Fields\Date;
use Rocket\Entities\Fields\Datetime;
use Rocket\Entities\Fields\Double;
use Rocket\Entities\Fields\Integer;
use Rocket\Entities\Fields\StringField;
use Rocket\Entities\Fields\Text;

class FieldValidationTest extends \Rocket\Utilities\TestCase
{
    public function providerFieldValid()
    {
        return [
            [StringField::class, 'string'],
            [StringField::class, 'test'],
            [StringField::class, 'title one'],
            [Text::class, 'title one'],
            [Text::class, str_repeat('x', 260)],
            [Integer::class, 1],
            [Integer::class, 10],
            [Integer::class, '1'],
            [Double::class, 1],
            [Double::class, M_PI],
            [Boolean::class, true],
            [Boolean::class, false],
            [Boolean::class, '1'],
            [Boolean::class, 0],
        ];
    }

    /**
     * @dataProvider providerFieldValid
     */
    public function testFieldValid($class, $string)
    {
        $field = new $class();
        $field->value = $string;

        $this->assertEquals($string, $field->value);
    }


    public function providerFieldInvalid()
    {
        return [
            [StringField::class, str_repeat('x', 260)],
            [StringField::class, new \stdClass()],
            [StringField::class, null],
            [Text::class, new \stdClass()],
            [Text::class, null],
            [Integer::class, null],
            [Integer::class, 1.5],
            [Integer::class, 'one'],
            [Double::class, null],
            [Double::class, 'zero'],
            [Boolean::class, null],
            [Boolean::class, 'off'],
            [Boolean::class, 'no'],
            [Boolean::class, 'yes'],
            [Date::class, null],
            [Date::class, 'today'],
            [Datetime::class, null],
        ];
    }

    /**
     * @dataProvider providerFieldInvalid
     */
    public function testFieldInvalid($class, $value)
    {
        $this->expectException(\Rocket\Entities\Exceptions\InvalidValueException::class);
        $field = new $class();
        $field->value = $value;
    }

    public function providerDateValid()
    {
        return [
            [Date::class, '1989-12-24', '1989-12-24 00:00:00'],
            [Date::class, Carbon::createFromDate(1989, 12, 24), '1989-12-24 00:00:00'],
            [Datetime::class, '1989-12-24 04:53:00', '1989-12-24 04:53:00'],
            [Datetime::class, Carbon::create(1989, 12, 24, 04, 53, 00), '1989-12-24 04:53:00'],
        ];
    }

    /**
     * @dataProvider providerDateValid
     */
    public function testValidDate($class, $string, $expected)
    {
        $field = new $class();
        $field->value = $string;

        $this->assertInstanceOf(Carbon::class, $field->value);
        $this->assertEquals($expected, $field->value->format('Y-m-d H:i:s'));
    }
}
