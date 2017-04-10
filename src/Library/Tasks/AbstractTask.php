<?php

/**
 * AbstractTask created at 2017-4-7 9:58:51
 * The encoding is UTF-8
 * 
 * @author skl@tzg.cn
 */

namespace swxbot\Library\Tasks;

use swxbot\Core\WeChat;

abstract class AbstractTask
{
    protected $_wc;
    
    public function __construct(WeChat $wc = null)
    {
        if($wc) {
            $this->_wc = $wc;
        }
    }
    
    public function setLogininfo(WeChat $wc)
    {
        $this->_wc = $wc;
    }
    
    public abstract function run($params = array());
    
}
