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
    private $_login_info;
    private $_listen_helper;

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
                } else if ($selector == '6') {
                    
                } else if ($selector == '7') {
                    Tools::console('你进入了手机微信');
                    sleep(10);
                } else if ($selector == '0') {
                    Tools::console('没有通知内容');
                }
            }
        } while($block);
    }

    public function sendMessage(AbstractMessage $message)
    {
        Tools::sendMessage($this->_login_info, $message);
    }

}
