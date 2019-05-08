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
        if(!$message['Content']) {
            return false;
        }

        $this->_userid = $message['FromUserName'];

        $post_data = array (
            'reqType' => 0,
            'perception' =>
                array (
                    'inputText' =>
                        array (
                            'text' => $message['Content'],
                        ),
                    'inputImage' =>
                        array (
                            'url' => 'imageUrl',
                        ),
                    'selfInfo' =>
                        array (
                            'location' =>
                                array (
                                    'city' => '',
                                    'province' => '',
                                    'street' => '',
                                ),
                        ),
                ),
            'userInfo' =>
                array (
                    'apiKey' => $this->_api_key,
                    'userId' => 'sss',
                ),
        );

        $data = $this->_http->request('POST', 'http://www.tuling123.com/openapi/api/v2', array(
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
        $this->_message_text->setName($this->_userid);
        $this->_data['results'] = array_reverse($this->_data['results']);
        switch($this->_data['intent']['code']) {
            case 10004:
                foreach ($this->_data['results'] as $res) {
                    $this->_message_text->setMessage($res['values'][$res['resultType']]);
                    $wc->sendMessage($this->_message_text);
                }
                break;
            case 10014:
                foreach ($this->_data['results'] as $res) {
                    $type = $res['resultType'];
                    if($type == 'url') {
                        $this->_img($res['values'][$res['resultType']]);
                        $wc->sendMessage($this->_message_img);
                    } else {
                        $this->_message_text->setMessage($res['values'][$res['resultType']]);
                        $wc->sendMessage($this->_message_text);
                    }
                }
                break;
            case 10008:
                break;
            default:
                $this->_notSupport();
                $wc->sendMessage($this->_message_text);
        }





//        switch($this->_data['intent']['code']) {
//            case '40002':;
//            case '100000':$this->_text();$wc->sendMessage($this->_message_text);break;
//            case '200000':$this->_img();$wc->sendMessage($this->_message_img);break;
//            case '302000':break;
//            case '308000':break;
//            case '313000':break;
//            case '314000':break;
//            default: {
//                $this->_notSupport();
//                $wc->sendMessage($this->_message_text);
//            }
//        }
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
    
    protected function _img($url)
    {
        if(!is_dir('wx_img')) {
            @mkdir('wx_img');
        }
        $fname = md5($url);
        $contents = $this->_http->request('GET', $url);
        $c = preg_match('/initData\>[\s\S]*?\</i', $contents, $d);
        //取第一张图片
        if($c) {
            $str = str_replace('initData>', '', str_replace('<', '', $d[0]));
            $json = json_decode($str, true);
            if($json) {
                $url = $json['initData']['list'][0]['thumb'];
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