<?php
/**
 * Created by IntelliJ IDEA.
 * User: onigoetz
 * Date: 20.03.14
 * Time: 21:53
 */

namespace Rocket\Utilities;

class Format
{
    /**
     * Get file size with correct extension
     *
     * @param int $size
     * @param string $retstring
     * @return string
     */
    public static function getReadableSize($bytes)
    {
        $unit = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $exp = floor(log($bytes, 1024)) | 0;
        return round($bytes / (pow(1024, $exp)), 2) . " $unit[$exp]";
    }

    /**
     * Format time
     *
     * @param integer $time
     * @return string
     */
    public static function getReadableTime($time)
    {
        if ($time < 1000) {
            return "{$time}ms";
        }

        $prefix = '';
        if ($time >= 60000) {
            $prefix = floor($time / 60000) . "m ";
            $time = $time % 60000;
        }

        return $prefix . number_format((float)($time / 1000), 3, '.', '') . "s";
    }
}
