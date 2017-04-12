<?php

/**
 * tzgLog created at 2017-4-11 16:28:29
 * The encoding is UTF-8
 * 
 * @author skl@tzg.cn
 */

namespace Library\Tasks;

use swxbot\Library\Helper\Tools;
use swxbot\Library\Message\MessageText;
use swxbot\Library\Tasks\AbstractTask;

class TzgLog extends AbstractTask
{
    
    private $_seek;
    
    private $_message;
    
    private $_info_line_count = 5;
    
    public function run($params = array())
    {
        $this->_message = new MessageText('sjdskl', '开始检测日志了');
        $this->_wc->sendMessage($this->_message);
        if(!isset($params['php-act-root-dir']) || !isset($params['sleep_time']) || !isset($params['monitor_files'])) {
            Tools::console('参数不正确');
            return false;
        }
        list($phpact_root, $sleep_time, $monitor_files) = array_values($params);
        $logpath = $this->_getPhpactLogPath($phpact_root);
        while(true) {
            $this->_analysisLog($monitor_files, $logpath);
            file_put_contents('seek', json_encode($this->_seek));
            sleep($sleep_time);
        }
    }
    
    protected function _analysisLog($monitor_files, $logpath)
    {
        if(!$this->_seek) {
            $this->_seek = $this->_readSeekInfo();
            if($this->_seek) {
                foreach($this->_seek as $k => $v) {
                    $this->_seek[$k]['fp'] = null;
                }
            }
        }
        foreach($monitor_files as $key => $file) {
            $f = $logpath . DIRECTORY_SEPARATOR . $file . "-" . date("Ymd") . ".log";
            if(!file_exists($f)) {
                $f = $logpath . DIRECTORY_SEPARATOR . $file . ".log";
                if(!file_exists($f)) {
                    continue;
                }
            }
            if(!isset($this->_seek[$f])) {
                $this->_seek[$f] = array(
                    'seek' => 0,
                    'line' => 0,
                    'fp'   => null,
                );
            }
            $this->_read($f);
        }
    }
    
    protected function _read($f)
    {
        if($this->_seek[$f]['fp'] == null) {
            $this->_seek[$f]['fp'] = fopen($f, 'r');
        }
        fseek($this->_seek[$f]['fp'], $this->_seek[$f]['seek']);
        while(($buffer = fgets($this->_seek[$f]['fp'], 8096)) !== false) {
            $this->_seek[$f]['line'] ++;
            $this->_seek[$f]['seek'] += strlen($buffer);
            if($this->_checkLogLine($buffer)) {
                for($i = 1; $i < $this->_info_line_count; $i ++) {
                    if(($buff_temp = fgets($this->_seek[$f]['fp'], 8096)) !== false) {
                        $buffer .= $buff_temp;
                        $this->_seek[$f]['line'] ++;
                        $this->_seek[$f]['seek'] += strlen($buff_temp);
                    }
                }
                $buffer = "错误信息,行:" . ($this->_seek[$f]['line'] - 4) . " --" .$buffer;
                $this->_message->setMessage($buffer);
                $this->_wc->sendMessage($this->_message);
            }
        }
        fclose($this->_seek[$f]['fp']);
        $this->_seek[$f]['fp'] = null;
    }
    
    protected function _checkLogLine($str)
    {
        $c = preg_match('/(warning)|(error)|(notice)/i', $str);
        if($c) {
            return true;
        }
        return false;
    }

    protected function _getPhpactLogPath($phpact_root)
    {
        $conf = $phpact_root . DIRECTORY_SEPARATOR ."conf" . DIRECTORY_SEPARATOR . "application.ini";
        $app = new \Yaf\Application($conf);
        return $app->getConfig()->get('log.path');
    }
    
    protected function _readSeekInfo()
    {
        if(!file_exists('seek')) {
            return array();
        }
        return json_decode(file_get_contents('seek'), true);
    }
    
    
}