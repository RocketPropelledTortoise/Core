<?php namespace Rocket\Utilities;

class FormatTest extends \PHPUnit_Framework_TestCase
{
    public function sizeProvider()
    {
        return [
            [10, '10 B'],
            [12068858100, '11.24 GB'],
            [25165824, '24 MB'],
        ];
    }

    /**
     * @dataProvider sizeProvider
     */
    public function testFormatSize($number, $expected)
    {
        // the str_replace is here because
        // depending on the system this is run
        // on the separator is a comma or a dot
        $this->assertEquals($expected, str_replace(',', '.', Format::getReadableSize($number)));
    }

    public function timeProvider()
    {
        return [
            [10, '10ms'],
            [1200, '1.200s'],
            [61000, '1m 1.000s'],
        ];
    }

    /**
     * @dataProvider timeProvider
     */
    public function testFormatTime($number, $expected)
    {
        $this->assertEquals($expected, Format::getReadableTime($number));
    }
}
