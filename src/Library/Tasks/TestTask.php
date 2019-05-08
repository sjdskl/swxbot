<?php

/**
 * TestTask created at 2017-4-7 10:06:08
 * The encoding is UTF-8
 * 
 * @author skl@tzg.cn
 */

namespace swxbot\Library\Tasks;

use swxbot\Library\Message\MessageText;

class TestTask extends AbstractTask
{
    public function run($params = array())
    {
        $memberList = $this->_wc->getMemberList();
        $nickname = 'sjdskl';
        $sendUser = '';
        foreach ($memberList as $id => $data) {
            if($data['NickName'] == $nickname) {
                $sendUser = $id;
            }
        }
        $message = new MessageText($sendUser, 'I am still alive!');
        while(true) {
            $this->_wc->sendMessage($message);
            sleep(300);
        }
    }
}