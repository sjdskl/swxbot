<?php

/**
 * TestTask created at 2017-4-7 10:06:08
 * The encoding is UTF-8
 * 
 * @author skl@tzg.cn
 */

namespace swxbot\Library\Tasks;

class TestTask extends AbstractTask
{
    public function run($params = array())
    {
        $message = new swxbot\Library\Message\MessageText('sjdskl', 'I am still alive!');
        while(true) {
            $this->_wc->sendMessage($message);
            sleep(300);
        }
    }
}