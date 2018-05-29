<?php

/**
 * config created at 2017-3-23 15:29:23
 * The encoding is UTF-8
 * 
 * @author skl@tzg.cn
 */

namespace swxbot\Conf;

class Config
{

    public static $_config = array(
        'daemon' => true,
        'env'    => 'c',//u=>ubuntu,c=>centos,w=>windows
        'debug' => false,
        'api' => array(
            'uuid' => 'https://login.weixin.qq.com/jslogin',
            'qrurl' => 'https://login.weixin.qq.com/l/',
            'waitlogin' => 'https://login.weixin.qq.com/cgi-bin/mmwebwx-bin/login',
            'webwxinit' => 'https://wx.qq.com/cgi-bin/mmwebwx-bin/webwxinit',
            'notify' => 'https://wx.qq.com/cgi-bin/mmwebwx-bin/webwxstatusnotify',
            'wxcontact' => 'https://wx.qq.com/cgi-bin/mmwebwx-bin/webwxgetcontact',
            'wxbatchgetcontact' => 'https://wx.qq.com/cgi-bin/mmwebwx-bin/webwxbatchgetcontact',
            'wxsendmsg' => 'https://wx.qq.com/cgi-bin/mmwebwx-bin/webwxsendmsg',
            'synccheck' => '/cgi-bin/mmwebwx-bin/synccheck',
            'webwxsync' => 'https://wx.qq.com/cgi-bin/mmwebwx-bin/webwxsync',
            'wxuploadmedia' => 'https://file.wx.qq.com/cgi-bin/mmwebwx-bin/webwxuploadmedia?f=json',
            'wxsendmsgimg' => 'https://wx.qq.com/cgi-bin/mmwebwx-bin/webwxsendmsgimg?fun=async&f=json',
            'wxsendappmsg' => 'https://wx.qq.com/cgi-bin/mmwebwx-bin/webwxsendappmsg?fun=async&f=json',
        ),
        'lang' => 'zh_CN',
        'appid' => 'wx782c26e4c19acffb',
        'cache' => 'cache.data',
        'log' => '',
    );

    private function __construct()
    {
        ;
    }

    private function __clone()
    {
        ;
    }
    
    /**
     * 获取配置key的值，可以使用api.uuid这种方式
     * 
     * @param string $key
     * @return boolean
     */
    public static function get($key = '')
    {
        $key_arr = explode(".", $key);
        $deps = count($key_arr);
        $value = self::$_config;
        for($i = 0; $i < $deps; $i ++) {
            if(!isset($value [$key_arr[$i]])) {
                return null;
            }
            $value = $value[$key_arr[$i]];
        }
        
        return $value;
    }
    
    /**
     * 运行中设置配置值
     * 
     * @param string $key
     * @param mixed $value
     * @return boolean
     */
    public static function set($key, $value)
    {
        $key_arr = explode(".", $key);
        $deps = count($key_arr);
        $v = &self::$_config;
        for($i = 0; $i < $deps; $i ++) {
            if(!isset($v [$key_arr[$i]])) {
                break;
            }
            $v = &$v[$key_arr[$i]];
        }
        for($j = $i; $j < $deps; $j ++) {
            $v[$key_arr[$j]] = array();
            $v = &$v[$key_arr[$j]];
        }
        $v = $value;
    }
}
