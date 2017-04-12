<?php

/**
 * TestListeMessage created at 2017-4-12 15:52:13
 * The encoding is UTF-8
 * 
 * @author skl@tzg.cn
 */

namespace swxbot\Library\ListenMessage;

use swxbot\Library\Helper\Tools;
use swxbot\Core\WeChat;
use swxbot\Library\Message\MessageText;

class TestListenMessage extends AbstractListenMessage
{
    public function run($message, WeChat $wc)
    {
        Tools::console('I receice message from ' . $message['FromUserName'] . " with contens '" . html_entity_decode($message['Content']) . "'");
        $wc->sendMessage(new MessageText($message['FromUserName'], '谢谢你的回复'));
    }
}