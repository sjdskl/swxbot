<?php

/**
 * Http created at 2017-4-1 16:15:05
 * The encoding is UTF-8
 * 
 * @author skl@tzg.cn
 */

namespace swxbot\Library\Helper;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use swxbot\Conf\Config;
use Requests_Session;
use swxbot\Library\Helper\Tools;

class HttpHelper
{
    private static $_instance;
    
    private $_http;
    
    private $_cookies;
    
    private function __construct()
    {
        if(Config::get('env') == 'c') {
//            修改证书位置
            Tools::setLocalCacertFile();
            $this->_cookies = new \Requests_Cookie_Jar();
            $this->_http = new \Requests_Session(null, array(), array(), array(
                'transport' => 'Requests_Transport_fsockopen',
                'timeout'  => 30,
                'connect_timeout' => 5,
                'cookies' => $this->_cookies,
            ));
        } else {
            $this->_cookies = new CookieJar;
            $this->_http = new Client(array(
                'cookies' => $this->_cookies,
            ));
        }
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
    
    public function request($method = 'GET', $url, $params = array(), $options = array())
    {
        try {
            if(Config::get('env') == 'u') {
                return $this->_guzzle($method, $url, $params, $options);
            } else {
                return $this->_request($method, $url, $params, $options);
            }
        } catch (\Exception $ex) {
            Tools::log('请求出错,' . $ex->getMessage());
        }
    }
    
    public function getCookieInfo($key = '')
    {
        if(Config::get('env') == 'u') {
            return $this->_guzzleCookie($key);
        } else {
            return $this->_requestCookie($key);
        }
    }
    
    public function upload($file, $url, $params = array(), $options = array(), $trys = 0)
    {
        Tools::console("上传文件:" . $file);
        if(!file_exists($file)){
            return false;
        }
        try {
            if(Config::get('env') == 'u') {
                return $this->_guzzleUpload($file, $url, $params, $options);
            } else {
                return $this->_requestUpload($file, $url, $params, $options);
            }
        } catch (\Exception $ex) {
            Tools::log('上传出错,' . $ex->getMessage());
            if($trys < 3) {
                Tools::console("上传重试第" . (++$trys) . "次");
                $this->upload($file, $url, $params, $options, $trys);
            }
        }
        
    }
    
    protected function _request($method = 'GET', $url, $params = array(), $options = array())
    {
        $header = array();
        if(isset($options['header'])) {
            $header = $options['header'];
        }
        if(isset($params['json'])) {
            $data = json_encode($params['json']);
            $header['Content-Type'] = 'application/json';
        } else if (isset($params['body'])) {
            $data = $params['body'];
            if(!isset($options['header'])) {
                $header['Content-Type'] = 'application/json';
            }
        } else {
            $data = $params;
        }
        return $this->_http->request($url, $header, $data, $method, $options)->body;
    }
    
    protected function _guzzle($method = 'GET', $url, $params = array(), $options = array())
    {
        $params = array_merge($params, $options);
        if($method == 'GET') {
            return (string)$this->_http->request($method, $url)->getBody();
        } else if($method == 'POST') {
            return (string)$this->_http->request($method, $url, $params)->getBody();
        }
    }
    
    protected function _requestCookie($key = '')
    {
        $it = $this->_cookies->getIterator();
        if($key && isset($it[$key])) {
            return $it[$key]->value; 
        }
        return '';
    }
    
    protected function _guzzleCookie($key = '')
    {
        $it = $this->_cookies->toArray();
        if($key) {
            foreach($it as $value) {
                if($value['Name'] == $key) {
                    return $value['Value'];
                }
            }
        }
        
        return '';
    }
    
    protected function _requestUpload($file, $url, $params = array(), $options = array())
    {
        foreach($params as $key => $value) {
            $pa[] = array(
                'name' => $key,
                'contents' => $value,
            );
        }
        $pa[] = array(
            'name' => 'filename',
            'contents' => fopen($file, 'r'),
        );
        $a = new \GuzzleHttp\Psr7\MultipartStream($pa);
        return $this->_http->request($url, array(
            'Content-Type' => 'multipart/form-data; boundary=' . $a->getBoundary(),
        ), $a->stream->getContents(), 'POST', $options)->body;
    }
    
    protected function _guzzleUpload($file, $url, $params = array(), $options = array())
    {
        foreach($params as $key => $value) {
            $pa[] = array(
                'name' => $key,
                'contents' => $value,
            );
        }
        $pa[] = array(
            'name' => 'filename',
            'contents' => fopen($file, 'r'),
//            'filename' => $file
        );
        $params['multipart'] = $pa;
        return (string)$this->_http->request('POST', $url, $params)->getBody();
    }
    
}