<?php

/**
 * MessageInterface created at 2017-4-1 17:46:41
 * The encoding is UTF-8
 * 
 * @author skl@tzg.cn
 */

namespace swxbot\Library\Message;

use swxbot\Conf\Config;
use swxbot\Library\Helper\Tools;
use swxbot\Library\Login\Logininfo;

abstract class AbstractMessage
{

    const MSG_TYPE_TEXT = 1;
    const MSG_TYPE_IMG = 3;
    const MSG_TYPE_EMOJI = 47;
    const MSG_TYPE_FILE = 6;
    const MSG_TYPE_LINK = 49;

    /** @var Logininfo $_login_info */
    protected $_login_info;
    //发送人员名称
    protected $_name;
    protected $_message;
    protected $_media;
    protected $_nickName;
    protected $_remarkName;

    protected function _getClientId()
    {
        return intval(microtime(true) * 1000) . mt_rand(1000, 9999);
    }

    protected function _uploadMedia()
    {
        if (!$this->_media || !file_exists($this->_media)) {
            return false;
        }
        $mime = Tools::getFileType($this->_media);
        $url = Config::get('api.wxuploadmedia');
        $stat = Tools::getFileStat($this->_media);
        $filesize = filesize($this->_media);
        $webwx_data_ticket = $this->_login_info->_http->getCookieInfo('webwx_data_ticket');
        $post_data = array_merge(array(
            "ClientMediaId" => $this->_getClientId(),
            "TotalLen" => strval($filesize),
            "StartPos" => 0,
            "DataLen" => $filesize,
            "MediaType" => 4,
            'UploadType' => 2,
            'ToUserName' => $this->_name,
        ), $this->_login_info->_base_request);
        $reqeust_data = array(
            'id' => 'WU_FILE_' . mt_rand(1, 1000),
            'name' => basename($this->_media),
            'type' => $mime,
            'lastModifieDate' => $stat['mtime'],
            'size' => strval($filesize),
            'mediatype' => stripos($mime, 'image') === false ? 'doc':'pic',
            'uploadmediarequest' => json_encode($post_data),
            'webwx_data_ticket' => $webwx_data_ticket,
            'pass_ticket' => $this->_login_info->_pass_ticket,
        );
        $header = array();
        $data = $this->_login_info->_http->upload($this->_media, $url, $reqeust_data, $header);
        $data = json_decode($data, true);
        if(!Tools::checkHttpResponse($data)) {
            return false;
        }
        if($data['BaseResponse']['Ret'] == 0) {
            return $data['MediaId'];
        }
        return false;
    }

    public function setLoginInfo(Logininfo $logininfo)
    {
        $this->_login_info = $logininfo;
    }

    public function setName($name)
    {
        $this->_name = $name;
    }

    public function setNickname($nickName)
    {
        $this->_nickName = $nickName;
    }
    
    public function getName()
    {
        return $this->_name;
    }

    public function getNickname()
    {
        return $this->_nickName;
    }

    public function setRemarkname($remarkName)
    {
        $this->_remarkName = $remarkName;
    }

    public function getRemarkname()
    {
        return $this->_remarkName;
    }

    public function setMessage($message)
    {
        $this->_message = $message;
    }

    public function setMedia($media)
    {
        $this->_media = $media;
    }

    public abstract function getMessageBody();

    public abstract function getUrl();
}
