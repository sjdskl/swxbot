<?php

/**
 * TuliingMessage created at 2017-4-13 14:25:40
 * The encoding is UTF-8
 * 
 * @author skl@tzg.cn
 */

namespace swxbot\Library\ListenMessage;

use swxbot\Library\ListenMessage\AbstractListenMessage;
use swxbot\Library\Helper\AesHelper;
use swxbot\Core\WeChat;
use swxbot\Library\Helper\Tools;
use swxbot\Library\Helper\HttpHelper;
use swxbot\Library\Message\MessageText;
use swxbot\Library\Message\MessageImg;

class TulingMessage extends AbstractListenMessage
{
    private $_api_key = '';
    
    private $_secret = '';
    
    private $_cmd = '';
    
    private $_userid = '';
    
    private $_http;
    
    private $_message_text = null;
    
    private $_message_img = null;
    
    private $_data = '';
    
    public function __construct($api_key, $secret)
    {
        $this->_api_key = $api_key;
        $this->_secret = $secret;
        $this->_http = HttpHelper::getInstance();
        $this->_message_img = new MessageImg;
        $this->_message_text = new MessageText;
    }
    
    public function run($message, WeChat $wc)
    {
        $this->_userid = $message['FromUserName'];
        $params = array(
            'key' => $this->_api_key,
            'info' => $message['Content'],
            'userid' => md5($this->_userid),
            'loc'    => ''
        );
        $timestamp = time();
        $key = $this->_secret . $timestamp . $this->_api_key;
        $post_data = array(
            'key' => $this->_api_key,
            'timestamp' => $timestamp,
            'data' => $this->encryption($params, $key),
        );
        
        $data = $this->_http->request('POST', 'http://www.tuling123.com/openapi/api', array(
            'json' => $post_data,
        ));
        
        Tools::console($data, 'tuling');
        
        $data = json_decode($data, true);
        if($data ) {
            $this->_data = $data;
            $this->_analysisMessage($wc);
        }
        return false;
    }
    
    protected function encryption($params, $key)
    {
        $data = AesHelper::encrypt(json_encode($params), $key);
        return $data;
    }
    
    /**
     * Code 说明
     * 100000 文本类
     * 200000 链接类
     * 302000 新闻类
     * 308000 菜谱类
     * 313000（儿童版） 儿歌类
     * 314000（儿童版） 诗词类
     * @param type $data
     */
    protected function _analysisMessage($wc)
    {
        switch($this->_data['code']) {
            case '100000':$this->_text();$wc->sendMessage($this->_message_text);break;
            case '200000':$this->_img();$wc->sendMessage($this->_message_img);break;
            case '302000':break;
            case '308000':break;
            case '313000':break;
            case '314000':break;
            default: {
                $this->_notSupport();
                $wc->sendMessage($this->_message_text);
            }
        }
    }
    
    protected function _notSupport()
    {
        $this->_message_text->setName($this->_userid);
        $this->_message_text->setMessage('暂时不支持该指令');
    }


    protected function _text()
    {
        $this->_message_text->setName($this->_userid);
        $this->_message_text->setMessage($this->_data['text']);
    }
    
    protected function _img()
    {
        if(!is_dir('wx_img')) {
            @mkdir('wx_img');
        }
        $fname = md5($this->_data['url']);
        $contents = $this->_http->request('GET', $this->_data['url']);
        $c = preg_match('/window\.initData\s=.+?;/i', $contents, $d);
        //取第一张图片
        if($c) {
            $str = str_replace('window.initData = ', '', str_replace(';', '', $d[0]));
            $json = json_decode($str, true);
            if($json) {
                $url = $json['list'][0]['thumb'];
                $info = pathinfo($url);
                $fname = $fname  . "." . $info['extension'];
                file_put_contents('wx_img/' . $fname, $this->_http->request('GET', $url));
            }
        }
        $this->_message_img->setName($this->_userid);
        $this->_message_img->setMedia('wx_img/' . $fname);
    }
    
    protected function _news()
    {
        
    }
    
    
    
    
    
}