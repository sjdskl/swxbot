<?php

/**
 * MessageImg created at 2017-4-5 10:10:12
 * The encoding is UTF-8
 * 
 * @author skl@tzg.cn
 */
namespace swxbot\Library\Message;

use swxbot\Library\Message\AbstractMessage;
use swxbot\Conf\Config;

class MessageImg extends AbstractMessage
{
    public function __construct($name = '', $media = '')
    {
        $this->_name = $name;
        $this->_media = $media;
    }
    
    
    public function getMessageBody()
    {
        //上传资源
        $media_id = $this->_uploadMedia();
        $client_msgid = $this->_getClientId();
        $post_data = array_merge($this->_login_info->_base_request, array(
            'Msg' => array(
                'ClientMsgId' => $client_msgid,
                'MediaId' => $media_id,
                'FromUserName' => $this->_login_info->_user['UserName'],
                'LocalID' => $client_msgid,
                'ToUserName' => $this->_name,
                'Type' => self::MSG_TYPE_IMG,
        )));
        
        return json_encode($post_data);
    }
    
    public function getUrl()
    {
        return Config::get('api.wxsendmsgimg') . "&" . http_build_query(array(
                    'lang' => Config::get('lang'),
                    'pass_ticket' => $this->_login_info->_pass_ticket,
        ));
    }
}