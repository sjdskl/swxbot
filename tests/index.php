<?php

/**
 * index created at 2017-4-11 16:21:31
 * The encoding is UTF-8
 * 
 * @author skl@tzg.cn
 */

set_time_limit(0);
define("APP_PATH", dirname(__FILE__));
require APP_PATH . '/../vendor/autoload.php';

$a = new swxbot\Core\WeChat(swxbot\Library\Login\Logininfo::getInstance());
$a->showQrCode();
$a->login();

$helper = new swxbot\Library\Helper\ListenMessageHelper();
//$helper->add('test', new swxbot\Library\ListenMessage\TestListenMessage());
//$helper->add('test2', new swxbot\Library\ListenMessage\TestListenMessage2());
$helper->add('tuling', new swxbot\Library\ListenMessage\TulingMessage('2c49d10fa88748d98f5d0d1e16111a6c', 'e510d47120b2b0f4'));
$a->setListenHelper($helper);

$process = new swxbot\Library\Progress\ProgressPcntl();
//$process->runBackground();
$process->run(function () use ($a) {
    $a->listenMessage();
}, 'swxbot-listen-message');

//$text = new \swxbot\Library\Message\MessageText('@a444c9aaaf3baa736f8b1b3a2723bcf3', '发现一个精彩的网站: https://gio.ren/r32746y');
//$a->sendMessage($text);

false && $process->runTask(new \swxbot\Library\Tasks\TestTask($a), [], 'test-task');


false && $process->runTask(new \Library\Tasks\TzgLog($a), 
    array(
//        'php-act-root-dir' => '/opt/web-data/php-act',
        'php-act-root-dir' => '/home/vagrant/s-framework',
        'sleep_time' => 5,
        'monitor_files' => array('sql_error')
    ), 
    'swxbot-log-notify'
);


$process->wait();
print_r($process->getProgresses());