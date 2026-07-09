<?php
namespace PapayasauceClasses;
/**
 * Description of newDateClass
 *
 * @author Yang Pao Thao
 */
class DoDebug {

    function DoDebug($thisstr)
    {
        $thisdir = '../../dodebug/debug.txt';
        if (!is_dir($thisdir)) {
            mkdir($thisdir, 0755, true);
        }
        file_put_contents($thisdir, $thisstr, FILE_APPEND);
    }
}
