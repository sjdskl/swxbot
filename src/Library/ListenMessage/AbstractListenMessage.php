<?php

/**
 * AbstractListenMessage created at 2017-4-12 15:50:50
 * The encoding is UTF-8
 * 
 * @author skl@tzg.cn
 */

namespace swxbot\Library\ListenMessage;

use swxbot\Core\WeChat;

abstract class AbstractListenMessage
{
    public abstract function run($message, WeChat $wc);
}