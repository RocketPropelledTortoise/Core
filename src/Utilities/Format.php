<?php
/**
 * Created by IntelliJ IDEA.
 * User: onigoetz
 * Date: 20.03.14
 * Time: 21:53
 */

namespace Rocket\Utilities;


class Format {
    /**
     * Get file size with correct extension
     *
     * @param int $size
     * @param string $retstring
     * @return string
     */
    public static function getReadableSize($size, $retstring = null)
    {
        // adapted from code at http://aidanlister.com/repos/v/function.size_readable.php
        $sizes = array('bytes', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');

        if ($retstring === null) {
            $retstring = '%01.2f %s';
        }

        $lastsizestring = end($sizes);

        foreach ($sizes as $sizestring) {
            if ($size < 1024) {
                break;
            }
            if ($sizestring != $lastsizestring) {
                $size /= 1024;
            }
        }
        if ($sizestring == $sizes[0]) {
            $retstring = '%01d %s';
        } // Bytes aren't normally fractional

        return sprintf($retstring, $size, $sizestring);
    }

    /**
     * Format time
     *
     * @param integer $time
     * @return string
     */
    public static function getReadableTime($time)
    {
        $ret = $time;
        $formatter = 0;
        $formats = array('ms', 's', 'm');
        if ($time >= 1000 && $time < 60000) {
            $formatter = 1;
            $ret = ($time / 1000);
        }
        if ($time >= 60000) {
            $formatter = 2;
            $ret = ($time / 1000) / 60;
        }
        $ret = number_format((float) $ret, 3, '.', '') . ' ' . $formats[$formatter];

        return $ret;
    }


}
