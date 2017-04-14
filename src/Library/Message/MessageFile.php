<?php

/**
 * MessageFile created at 2017-4-6 11:17:04
 * The encoding is UTF-8
 * 
 * @author skl@tzg.cn
 */

namespace swxbot\Library\Message;

use swxbot\Library\Message\AbstractMessage;
use swxbot\Conf\Config;

class MessageFile extends AbstractMessage
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
        $pathinfo = pathinfo($this->_media);
        $post_data = array_merge($this->_login_info->_base_request, array(
            'Msg' => array(
                'ClientMsgId' => $client_msgid,
                'Content' => "<appmsg appid='wxeb7ec651dd0aefa9' sdkver=''><title>{$this->_media}</title><des></des><action></action><type>" . self::MSG_TYPE_FILE . "</type><content></content><url></url><lowurl></lowurl><appattach><totallen>" . filesize($this->_media) . "</totallen><attachid>{$media_id}</attachid><fileext>{$pathinfo['extension']}</fileext></appattach><extinfo></extinfo></appmsg>",
                'FromUserName' => $this->_login_info->_user['UserName'],
                'LocalID' => $client_msgid,
                'ToUserName' => $this->_name,
                'Type' => self::MSG_TYPE_FILE,
        )));
        $post_data['Scene'] = 0;
                
        return json_encode($post_data, JSON_UNESCAPED_SLASHES);
    }

    public function getUrl()
    {
        return Config::get('api.wxsendappmsg') . "&" . http_build_query(array(
                    'lang' => Config::get('lang'),
                    'pass_ticket' => $this->_login_info->_pass_ticket,
        ));
    }

}
