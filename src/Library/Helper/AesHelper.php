<?php

/**
 * AesHelper created at 2017-4-13 15:25:07
 * The encoding is UTF-8
 * 
 * @author skl@tzg.cn
 */

namespace swxbot\Library\Helper;

class AesHelper
{
    private static $iv = [0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00];

    public static function encrypt($param, $key)
    {
        $key = md5($key);
        $str = $param;
        // PKCS5 padding
        $pad = 16 - (strlen($str) % 16);
        $str .= str_repeat(chr($pad), $pad);
        $key = hash('md5', $key, true);
        $iv = implode(array_map("chr", self::$iv));;
        $td = mcrypt_module_open('rijndael-128', '', 'cbc', '');
        mcrypt_generic_init($td, $key, $iv);
        $encrypted = mcrypt_generic($td, $str);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return base64_encode($encrypted);
    }

}
