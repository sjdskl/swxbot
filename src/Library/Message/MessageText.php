<?php

/**
 * MessageText created at 2017-4-5 9:52:26
 * The encoding is UTF-8
 * 
 * @author skl@tzg.cn
 */
namespace swxbot\Library\Message;

use swxbot\Library\Message\AbstractMessage;
use swxbot\Conf\Config;
use swxbot\Library\Helper\Tools;

class MessageText extends AbstractMessage
{

    public function __construct($name = '', $message = '')
    {
        $this->_name = $name;
        $this->_message = $message;
    }
    
    public function getMessageBody()
    {
        $client_msgid = $this->_getClientId();
        $post_data = array_merge($this->_login_info->_base_request, array(
            'Msg' => array(
                'ClientMsgId' => $client_msgid,
                'Content' => $this->_message,
                'FromUserName' => $this->_login_info->_user['UserName'],
                'LocalID' => $client_msgid,
                'ToUserName' => $this->_name,
                'Type' => self::MSG_TYPE_TEXT,
        )));
//        Tools::console($post_data);
        return json_encode($post_data, JSON_UNESCAPED_UNICODE);
    }

    public function getUrl()
    {
        return Config::get('api.wxsendmsg') . "?" . http_build_query(array(
                    'lang' => Config::get('lang'),
                    'pass_ticket' => $this->_login_info->_pass_ticket,
        ));
    }

}
