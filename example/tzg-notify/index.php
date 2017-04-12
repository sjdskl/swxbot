<?php

/**
 * index created at 2017-4-11 16:21:31
 * The encoding is UTF-8
 * 
 * @author skl@tzg.cn
 */
set_time_limit(0);
define("APP_PATH", dirname(__FILE__));
require APP_PATH . '/vendor/autoload.php';

$a = new swxbot\Core\WeChat(swxbot\Library\Login\Logininfo::getInstance());
$a->showQrCode();
$a->login();

$process = new swxbot\Library\Progress\ProgressPcntl();


$process->runTask(new \Library\Tasks\TzgLog($a), 
    array(
        'php-act-root-dir' => '/home/vagrant/s-framework',
        'sleep_time' => 5,
        'monitor_files' => array('sql_error')
    ), 
    'loglogloglog');


$process->wait();
print_r($process->getProgresses());