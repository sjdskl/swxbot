<?php

/**
 * MessageLink created at 2017-4-14 13:44:45
 * The encoding is UTF-8
 * 
 * @author skl@tzg.cn
 */

namespace swxbot\Library\Message;

use swxbot\Library\Message\AbstractMessage;
use swxbot\Conf\Config;
use swxbot\Library\Helper\Tools;

class MessageLink extends AbstractMessage
{
    protected $_url;
    protected $_filename;
    
    public function __construct($url = '', $filename = '')
    {
        $this->_url = $url;
        $this->_filename = $filename;
    }
    
    public function setUrl($url)
    {
        $this->_url = $url;
    }
    
    public function setFilename($filename)
    {
        $this->_filename = $filename;
    }

    public function getMessageBody()
    {
        $client_msgid = $this->_getClientId();
        $post_data = array_merge($this->_login_info->_base_request, array(
            'Msg' => array(
                'ClientMsgId' => $client_msgid,
                'Content' => "<?xml version=\"1.0\"?><msg><appmsg appid=\"\" sdkver=\"0\"><title>{$this->_filename}</title><des>{$this->_url}</des><type>5</type><content></content><url></url><thumburl></thumburl></appmsg><appinfo><version></version><appname></appname></appinfo></msg>",
                'FromUserName' => $this->_login_info->_user['UserName'],
                'LocalID' => $client_msgid,
                'Url' => $this->_url,
                'FileName' =>  $this->_filename,
                'ToUserName' => $this->_name,
                'Type' => self::MSG_TYPE_LINK,
        )));
        return json_encode($post_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function getUrl()
    {
        return Config::get('api.wxsendmsg') . "?" . http_build_query(array(
                    'lang' => Config::get('lang'),
                    'pass_ticket' => $this->_login_info->_pass_ticket,
        ));
    }

}
