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

$helper = new swxbot\Library\Helper\ListenMessageHelper();
$helper->add('test', new swxbot\Library\ListenMessage\TestListenMessage());
$helper->add('test2', new swxbot\Library\ListenMessage\TestListenMessage2());
$a->setListenHelper($helper);

$process = new swxbot\Library\Progress\ProgressPcntl();
$process->runBackground();
$process->run(function () use ($a) {
    $a->listenMessage();
}, 'swxbot-listen-message');
$process->runTask(new \Library\Tasks\TzgLog($a), 
    array(
        'php-act-root-dir' => '/opt/web-data/php-act',
        'sleep_time' => 5,
        'monitor_files' => array('sql_error')
    ), 
    'swxbot-log-notify'
);


$process->wait();
print_r($process->getProgresses());