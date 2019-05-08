<?php

/**
 * Core created at 2017-3-31 14:31:10
 * The encoding is UTF-8
 * 
 * @author skl@tzg.cn
 */

namespace swxbot\Core;

use swxbot\Conf\Config;
use swxbot\Library\Helper\Tools;
use swxbot\Library\Message\AbstractMessage;
use swxbot\Library\Helper\ListenMessageHelper;
use swxbot\Library\Login\Logininfo;
use Requests;

class WeChat
{

    private $_config;
    /** @var Logininfo $_login_info */
    private $_login_info;
    private $_listen_helper;
    //昵称和标记对应用户名映射缓存
    private $_cache_map = [];

    public function __construct(Logininfo $logininfo = null, $listen_helper = null)
    {
        $this->_config = Config::$_config;
        date_default_timezone_set('Asia/Shanghai');
        $logininfo && $this->setLogininfo($logininfo);
        $listen_helper && $this->setListenHelper($listen_helper);
        //判断依赖的扩展;
        $this->_checkExt();
    }
    
    /**
     * 检查扩展
     */
    protected function _checkExt()
    {
        return true;
    }

    public function setLogininfo(Logininfo $logininfo)
    {
        $this->_login_info = $logininfo;
    }

    public function getLoginInfo()
    {
        return $this->_login_info;
    }

    public function getMemberList()
    {
        return $this->_login_info->_member_list;
    }

    public function getPublicList()
    {
        return $this->_login_info->_public_user_list;
    }

    public function getGroupList()
    {
        return $this->_login_info->_group_list;
    }
    
    public function setListenHelper(ListenMessageHelper $listen_helper)
    {
        $this->_listen_helper = $listen_helper;
    }

    protected function _analysisMsg($r)
    {
        return Tools::analysisMsg($r);
    }
    
        
    protected function _receiveMsg()
    {
        return Tools::receiveMessage($this->_login_info);
    }

    /**
     * 显示二维码
     */
    public function showQrCode()
    {
        $url = $this->_config['api']['uuid'];
        $params = array(
            'appid' => $this->_config['appid'],
            'fun' => 'new',
            'lang' => $this->_config['lang'],
            '_' => intval(microtime(true)),
        );
        Tools::setLocalCacertFile();
        $data = Requests::post($url, array(), $params, array(
                    'transport' => 'Requests_Transport_fsockopen',
                    'timeout' => 30,
                    'connect_timeout' => 30,
                ))->body;
        Tools::checkHttpResponse($data);
        preg_match('/window.QRLogin.code = (\d+); window.QRLogin.uuid = "(\S+?)"/i', $data, $t);
        if ($t) {
            $this->_login_info->_uuid = $t[2];
        }
        if ($t[1] == '200') {
            //输出二维码
            Tools::echoQrCode($this->_login_info->_uuid);
        } else {
            Tools::console("获取UUID出错", 'error');
        }
    }

    public function login()
    {
        $this->_login_info->login();
    }

    public function listenMessage($block = true)
    {
        do {
            $t = Tools::getMicrotime();
            list($retcode, $selector) = Tools::syncCheck($this->_login_info->_webpush_host, $this->_login_info);
            if ($retcode == '1100') {
                Tools::console('你在手机上登出了微信，债见');
            } else if ($retcode == '1101') {
                Tools::console('你在其他地方登录了 WEB 版微信，债见');
            } else if ($retcode == 0) {
                if ($selector == '2') {
                    $r = $this->_receiveMsg();
                    $this->_analysisMsg($r);
                    if($this->_listen_helper && !$this->_listen_helper->isEmpty()) {
                        $this->_listen_helper->dispatch($r['AddMsgList'], $this);
                    }
                } else if ($selector == '6' || $selector == '3') {
                    $r = $this->_receiveMsg();
                    Tools::console('selector=' . $selector . ",result=" . json_encode($r));
                    $this->_analysisMsg($r);
                    Tools::console("貌似出错了");
//                    break;
                } else if ($selector == '7') {
                    Tools::console('你进入了手机微信');
                    sleep(10);
                } else if ($selector == '0') {
                    Tools::console('没有通知内容');
                }
            }
            //当快速循环时，增加sleep时间
            $t = Tools::getMicrotime() - $t;
            if($t < 5) {
                sleep(5);
            }
        } while($block);
    }

    public function sendMessage(AbstractMessage $message)
    {
        //convert name
        //check if set nickname
        if($message->getNickname() || $message->getRemarkname()) {
            if(isset($this->_cache_map['nick'][$message->getNickname()])) {
                $message->setName($this->_cache_map['nick'][$message->getNickname()]);
            } else if(isset($this->_cache_map['remark'][$message->getRemarkname()])) {
                $message->setName($this->_cache_map['remark'][$message->getRemarkname()]);
            } else {
                foreach($this->_login_info->_member_list as $m) {
                    if(($message->getNickname() && $m['NickName'] == $message->getNickname()) || ($message->getRemarkname() && $m['RemarkName'] == $message->getRemarkname())) {
                        $message->setName($m['UserName']);
                        if($message->getNickname()) {
                            $this->_cache_map['nick'][$message->getNickname()] = $m['UserName'];
                        } else if($message->getRemarkname()) {
                            $this->_cache_map['remark'][$message->getRemarkname()] = $m['UserName'];
                        }
                        break;
                    }
                }
            }
        }
        Tools::sendMessage($this->_login_info, $message);
    }

}
