<?php

/**
 * LoginInfo created at 2017-3-31 16:28:13
 * The encoding is UTF-8
 * 
 * @author skl@tzg.cn
 */

namespace swxbot\Library\Login;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Client;
use swxbot\Conf\Config;
use swxbot\Library\Helper\Tools;
use swxbot\Library\Helper\HttpHelper;

class Logininfo
{

    private static $_instance;
    public $_jar;
    public $_debug = false;
    public $_http;
    public $_deviceid;
    public $_uuid;
    public $_redirect_url;
    public $_skey, $_sid, $_uin, $_pass_ticket;
    public $_base_request, $_init_data;
    public $_sync_key_arr, $_sync_key, $_user;
    public $_member_count, $_member_list;
    public $_public_user_list, $_group_list, $_group_member_list;
    public $_special_users = array('newsapp', 'fmessage', 'filehelper', 'weibo', 'qqmail', 'fmessage', 'tmessage', 'qmessage', 'qqsync', 'floatbottle', 'lbsapp', 'shakeapp', 'medianote', 'qqfriend', 'readerapp', 'blogapp', 'facebookapp', 'masssendapp', 'meishiapp', 'feedsapp',
        'voip', 'blogappweixin', 'weixin', 'brandsessionholder', 'weixinreminder', 'wxid_novlwrv3lqwv11', 'gh_22b87fa7cb3c', 'officialaccounts', 'notification_messages', 'wxid_novlwrv3lqwv11', 'gh_22b87fa7cb3c', 'wxitil', 'userexperience_alarm', 'notification_messages');
    public $_webpush_host;

    private function __construct()
    {
        $this->_deviceid = $this->_deviceid();
        $this->_initHttp();
    }

    private function __clone()
    {
        ;
    }

    public static function getInstance()
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    //登录操作
    public function login()
    {
        $this->_run('_waitLogin');
        $this->_run('_waitLogin', 0);
        $this->_run('_redirect', null, 3);
        $this->_run('_initWeixin', null, 3);
        $this->_run('_notify', null, 3);
        $this->_run('_contact', null, 3);
        Tools::console('共有:' . $this->_member_count . "个好友", "info");
        Tools::console('共有:' . count($this->_group_list) . "个群," . count($this->_member_list) . "个联系人," . count($this->_public_user_list) . "个公众号或服务号");
//        $this->_run('_getbatchcontact');
        $this->_run('_testSyncCheck', null, 3);
    }
    
    protected function _run($method, $param = null, $trys = 0)
    {
        Tools::console('开始执行' . $method);
        if($param !== null) {
            $flag = $this->$method($param);
        } else {
            $flag = $this->$method();
        }
//        sleep(1);
        if(!$flag) {
            Tools::console('执行' . $method . '失败' );
            //重试
            if($trys > 0) {
                Tools::console('重新执行执行' . $method . ",trys=" . ($trys - 1));
                $this->_run($method, $param, -- $trys);
            } else {
                exit;
            }
        } 
    }

    protected function _deviceid()
    {
        $deviceid = 'e';
        for ($i = 0; $i < 15; $i ++) {
            $deviceid .= mt_rand(0, 9);
        }

        return $deviceid;
    }

    protected function _initHttp()
    {
        $this->_http = HttpHelper::getInstance();
    }

    protected function _waitLogin($tip = 1)
    {
        sleep($tip);
        $url = Config::get('api.waitlogin') . "?" . http_build_query(array(
                    'uuid' => $this->_uuid,
                    'tip' => $tip,
                    '_' => intval(microtime(true)),
        ));
        $data = $this->_http->request('GET', $url);
        if (!Tools::checkHttpResponse($data)) {
            return false;
        }
        preg_match("/window.code=(\d+);/i", $data, $t);
        if ($t) {
            $code = $t[1];
            if ($code == '200') {
                preg_match("/window.redirect_uri=\"(\S+?)\";/i", $data, $t);
                $this->_redirect_url = $t[1] . "&fun=new";
                return true;
            } else if ($code == '201') {
                return true;
            } else if ($code == '408') {
                Tools::console("扫描超时", 'error');
            } else {
                Tools::console("扫描出错", 'error');
            }
        }
        Tools::console("扫描出错", 'error');
        return false;
    }

    protected function _redirect()
    {
        $data = $this->_http->request('GET', $this->_redirect_url);
        if (!Tools::checkHttpResponse($data)) {
            return false;
        }
        $xml = simplexml_load_string($data);
        $this->_skey = (string)$xml->skey;
        $this->_sid = (string)$xml->wxsid;
        $this->_uin = (string)$xml->wxuin;
        $this->_pass_ticket = (string)$xml->pass_ticket;
        if (!$this->_skey || !$this->_sid || !$this->_uin || !$this->_pass_ticket) {
            Tools::console("redirect获取参数错误", 'error');
            return false;
        }
        $this->_base_request = array(
            'BaseRequest' => array(
                'Uin' => intval($this->_uin),
                'Sid' => $this->_sid,
                'Skey' => $this->_skey,
                'DeviceID' => $this->_deviceid,
            )
        );
        return true;
    }

    protected function _initWeixin()
    {
        $url = Config::get('api.webwxinit') . "?" . http_build_query(array(
                    'r' => intval(microtime(true) * 100),
                    'pass_ticket' => $this->_pass_ticket,
                    'skey' => $this->_skey,
        ));
        $post_data = $this->_base_request;
        $data = $this->_http->request('POST', $url, array(
            'json' => $post_data,
        ));
        if (!Tools::checkHttpResponse($data)) {
            return false;
        }
        $this->_init_data = json_decode($data, true);
        if ($this->_init_data['BaseResponse']['Ret'] != 0) {
            Tools::console('initWeixin获取数据出错了', 'error');
            return false;
        }
        $this->_sync_key_arr = $this->_init_data['SyncKey'];
        $this->_sync_key = Tools::syncKey($this->_init_data['SyncKey']);
        $this->_user = $this->_init_data['User'];
        return true;
    }

    protected function _notify()
    {
        $url = Config::get('api.notify') . "?" . http_build_query(array(
                    'lang' => Config::get('lang'),
                    'pass_ticket' => $this->_pass_ticket,
        ));

        $post_data = array_merge($this->_base_request, array(
            "Code" => 3,
            "FromUserName" => $this->_user['UserName'],
            "ToUserName" => $this->_user['UserName'],
            "ClientMsgId" => intval(microtime(true)),
        ));
        $data = $this->_http->request('POST', $url, array(
            'json' => $post_data,
        ));
        if (!Tools::checkHttpResponse($data)) {
            return false;
        }
        $data = json_decode($data, true);
        if ($data['BaseResponse']['Ret'] != 0) {
            Tools::console('notify获取数据出错了', 'error');
            return false;
        }
        return true;
    }

    protected function _contact()
    {
        $url = Config::get('api.wxcontact') . "?" . http_build_query(array(
                    'pass_ticket' => $this->_pass_ticket,
                    'skey' => $this->_skey,
                    'r' => intval(microtime(true) * 100),
                    'lang' => Config::get('lang'),
        ));
        $data = $this->_http->request('GET', $url);
        $data = json_decode($data, true);
        if (!Tools::checkHttpResponse($data)) {
            return false;
        }
        $this->_member_count = $data['MemberCount'];
        $this->_member_list = $data['MemberList'];
        foreach ($this->_member_list as $key => $row) {
            if ($row['VerifyFlag'] & 8) {//公众号or服务号
                unset($this->_member_list[$key]);
                $this->_public_user_list[] = $row;
            } else if (in_array($row['UserName'], $this->_special_users)) {
                unset($this->_member_list[$key]);
                $this->_special_users[] = $row;
            } else if (stripos($row['UserName'], '@@') !== false) {//群聊
                unset($this->_member_list[$key]);
                $this->_group_list[] = $row;
            } else if ($row['UserName'] == $this->_user['UserName']) {
                unset($this->_member_list[$key]);
            }
        }
        foreach($this->_member_list as $key => $value) {
            $t[$value['UserName']] = $value;
        }
        $this->_member_list = $t;
        return true;
    }

    protected function _getbatchcontact()
    {
        if (!$this->_group_list || !count($this->_group_list)) {
            return true;
        }
        $url = Config::get('api.wxbatchgetcontact') . "?" . http_build_query(array(
            'type' => 'ex',
            'pass_ticket' => $this->_pass_ticket,
            'r' => intval(microtime(true) * 100),
        ));

        $post_data = array_merge($this->_base_request, array(
            "Count" => count($this->_group_list),
            "List" => Tools::groupList($this->_group_list),
        ));
        $data = $this->_http->request('POST', $url, array(
            'json' => $post_data
        ));
        $data = json_decode($data, true);
        if (!Tools::checkHttpResponse($data)) {
            return false;
        }
        if ($data && isset($data['MemberList']) && is_array($data['MemberList'])) {
            foreach ($data['MemberList'] as $row) {
                $this->_group_member_list[] = $row;
            }
        }
        return true;
    }

    protected function _testSyncCheck()
    {
        $hosts = [
            'webpush.wx.qq.com',
            'wx2.qq.com',
            'webpush.wx2.qq.com',
            'wx8.qq.com',
            'webpush.wx8.qq.com',
            'web2.wechat.com',
            'webpush.web2.wechat.com',
            'wechat.com',
            'webpush.web.wechat.com',
            'webpush.weixin.qq.com',
            'webpush.wechat.com',
            'webpush1.wechat.com',
            'webpush2.wechat.com',
            'webpush2.wx.qq.com'
        ];

        foreach ($hosts as $host) {
            $this->_webpush_host = $host;
            list($retcode, $selector) = Tools::syncCheck($host, $this);
            if ($retcode == '0') {
                Tools::console("useful url is " . $this->_webpush_host);
                return true;
            }
        }
        return false;
    }

}
