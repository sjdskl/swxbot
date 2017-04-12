<?php

/**
 * ListenMessageHelper created at 2017-4-12 15:58:40
 * The encoding is UTF-8
 * 
 * @author skl@tzg.cn
 */

namespace swxbot\Library\Helper;

use swxbot\Library\ListenMessage\AbstractListenMessage;
use swxbot\Core\WeChat;

class ListenMessageHelper
{
    private $_listen = array();
    
    public function __construct()
    {
        
    }
    
    public function add($key, AbstractListenMessage $listen)
    {
        $this->_listen[$key] = $listen;
    }
    
    public function isEmpty()
    {
        if(empty($this->_listen)) {
            return true;
        }
        
        return false;
    }
    
    public function dispatch($message_array, WeChat $wc)
    {
        if(empty($this->_listen) || empty($message_array)) {
            return true;
        }
        foreach($message_array as $message) {
            foreach($this->_listen as $key => $obj) {
                $flag = $obj->run($message, $wc);
                Tools::console($key . " 处理消息完成, flag={$flag}");
            }
        }
    }
    
    
}