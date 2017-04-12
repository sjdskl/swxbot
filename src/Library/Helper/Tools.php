<?php

/**
 * Tools created at 2017-3-31 14:38:06
 * The encoding is UTF-8
 * 
 * @author skl@tzg.cn
 */

namespace swxbot\Library\Helper;

use swxbot\Conf\Config;
use BaconQrCode\Encoder\Encoder;
use BaconQrCode\Common\ErrorCorrectionLevel;
use swxbot\Library\Helper\QrCodePalnHelper;
use swxbot\Library\Login\Logininfo;
use swxbot\Library\Message\AbstractMessage;

class Tools
{

    private function __construct()
    {
        ;
    }

    private function __clone()
    {
        ;
    }

    public static function console($msg, $level = 'info', $desc = '')
    {
        if (is_array($msg)) {
            $msg = var_export($msg, true);
        }
        if (Config::get('daemon')) {
            return self::log($msg, $level, $desc);
        }
        $msg = date('Y-m-d H:i:s') . " " . $desc . " " . $level . ":" . $msg . "\n";
        echo $msg;
    }

    public static function log($msg, $level = 'info', $desc = '')
    {
        if (is_array($msg)) {
            $msg = var_export($msg, true);
        }
        $msg = date('Y-m-d H:i:s') . " " . $desc . " " . $level . ":" . $msg . "\n";
        file_put_contents("log-" . date('Y-m-d') . "", $msg, FILE_APPEND);
    }
    
    public static function setLocalCacertFile($n = 'cacert.pem')
    {
        if(file_exists($n)) {
            \Requests::set_certificate_path($n);
        } else if(file_exists('src' . DIRECTORY_SEPARATOR . $n)){
            \Requests::set_certificate_path('src' . DIRECTORY_SEPARATOR . $n);
        } else {
            return false;
        }
    }

    public static function checkHttpResponse($data)
    {
        if (Config::get('debug')) {
            self::console($data, 'debug');
        }
        if (!$data) {
            return false;
        }

        return true;
    }

    public static function echoQrCode($uuid)
    {
        $str = Config::get('api.qrurl') . $uuid;
        $qrCode = Encoder::encode(
                        $str, new ErrorCorrectionLevel(ErrorCorrectionLevel::L), Encoder::DEFAULT_BYTE_MODE_ECODING
        );
        $renderer = new QrCodePalnHelper();
        echo $renderer->render($qrCode);
    }

    public static function syncKey($data)
    {
        $str = array();
        foreach ($data['List'] as $row) {
            $str[] = $row['Key'] . "_" . $row['Val'];
        }
        return implode("|", $str);
    }
    
    public static function receiveMessage(Logininfo $logininfo)
    {
        $url = Config::get('api.webwxsync') . "?" . http_build_query(array(
            'sid' => $logininfo->_sid,
            'skey' => $logininfo->_skey,
            'pass_ticket' => $logininfo->_pass_ticket,
        ));
        
        $post_data = array_merge($logininfo->_base_request, array(
            'SyncKey' => $logininfo->_sync_key_arr,
            'rr' => ~intval(microtime(true) * 1000),
        ));
        $data = $logininfo->_http->request('POST', $url, array(
            'json' => $post_data
        ));
        $data = json_decode($data, true);
        if (!Tools::checkHttpResponse($data)) {
            return false;
        }
        if($data['BaseResponse']['Ret'] == '0') {
            $logininfo->_sync_key_arr = $data['SyncKey'];
            $logininfo->_sync_key = Tools::syncKey($data['SyncKey']);
        }
        return $data;
    }
    
    public static function sendMessage(Logininfo $logininfo, AbstractMessage $message)
    {
        $message->setLoginInfo($logininfo);
        $data = $logininfo->_http->request('POST', $message->getUrl(), array(
            'body' => $message->getMessageBody(),
        ), array(
            'Content-Type' => 'application/json'
        ));
        $data = json_decode($data, true);
        if (!self::checkHttpResponse($data)) {
            self::console('发送失败');
            return false;
        }
        self::console($data['BaseResponse']['Ret'] == 0 ? '发送成功' : '发送失败', 'info');
    }
    
    public static function analysisMsg($r)
    {
        foreach($r['AddMsgList'] as $msg) {
            $type = $msg['MsgType'];
            $name = $msg['FromUserName'];
            $content = html_entity_decode($msg['Content']);
            self::console($type . "->" . $name . "->" . $content, 'new msg');
            
//            //回复一个内容
//            if($name != $this->_login_info->_user['UserName']) {
//                $this->sendMessage(new \Library\Message\MessageText($name, '谢谢你的回复'));
//            }
        }
    }

    public static function syncCheck($host, Logininfo $logininfo)
    {
        $url = "https://" . $host . Config::get('api.synccheck') . "?" . http_build_query(array(
                    'r' => intval(microtime(true) * 1000),
                    'sid' => $logininfo->_sid,
                    'uin' => $logininfo->_uin,
                    'skey' => $logininfo->_skey,
                    'deviceid' => $logininfo->_deviceid,
                    'synckey' => $logininfo->_sync_key,
                    '_' => intval(microtime(true) * 1000),
        ));
        self::console($url);
        $data = $logininfo->_http->request('GET', $url);
        if (!self::checkHttpResponse($data)) {
            self::console('synccheck失败');
            return false;
        }
        preg_match("/window.synccheck={retcode:\"(\d+)\",selector:\"(\d+)\"}/i", $data, $t);
        return $t ? [$t[1], $t[2]] : [-1, -1];
    }
    
    public static function getFileType($filename)
    {
        if(!file_exists($filename)) {
            return false;
        }
        $finfo = finfo_open(FILEINFO_MIME);
        $mimetype = finfo_file($finfo, $filename);
        finfo_close($finfo);
        return $mimetype;
    }
    
    public static function getFileStat($filename)
    {
        if(!file_exists($filename)) {
            return false;
        }
        return stat($filename);
    }
    
    public static function groupList($group_list)
    {
        $t = array();
        if(!$group_list) {
            return $t;
        }
        foreach($group_list as $row) {
            $t[] = array(
                'UserName' => $row['UserName'],
                'EncryChatRoomId' => ''
            );
        }

        return $t;
    }


}
