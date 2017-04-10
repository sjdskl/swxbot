<?php

/**
 * ProgressPcntl created at 2017-4-6 14:59:06
 * The encoding is UTF-8
 * 
 * @author skl@tzg.cn
 */

namespace swxbot\Library\Progress;

use swxbot\Library\Helper\Tools;

class ProgressPcntl extends AbstractProgress
{
    
    public function runBackground()
    {
        $pid = pcntl_fork();
        if($pid == -1) {
            Tools::console('创建子进程失败');
            return false;
        } else if($pid) {
            Tools::console('父进程退出');
            exit;
        }
        //获取分组ID
        $sid = posix_setsid();
        if($sid == -1) {
            Tools::console('posix_setsid失败');
            exit;
        }
    }
    
    public function run(callable $callback)
    {
        $pid = pcntl_fork();
        if($pid == -1) {
            Tools::console('创建子进程失败');
            return false;
        }
        if($pid == 0) {
            $callback();
            exit(0);
        } else {
            $this->_progress[$pid] = array(
                'pid' => $pid,
                'status' => 1,//1run,0finish
            );
        }
    }
    
    public function wait()
    {
        $flag = true;
        do {
            $flag = false;
            foreach($this->_progress as $pid => $progress) {
                if($progress['status'] == 0) {
                    continue;
                }
                $exit_pid = pcntl_waitpid($pid, $status, WNOHANG);
                if($exit_pid > 0) {
                    $this->_progress[$exit_pid]['status'] = 0;
                } else if($exit_pid == -1){
                    $this->_progress[$pid]['status'] = -1;
                } else {
                    $flag = true;
                }
            }
            usleep(100000);
        } while($flag);
    }

}