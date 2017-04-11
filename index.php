<?php

/**
 * weixin created at 2017-3-31 14:24:07
 * The encoding is UTF-8
 * 
 * @author skl@tzg.cn
 */

namespace swxbot;

set_time_limit(0);
define("APP_PATH", dirname(__FILE__));
require APP_PATH . '/vendor/autoload.php';

$a = new Core\WeChat(Library\Login\Logininfo::getInstance());
$a->showQrCode();
$a->login();

//$process = new Library\Progress\ProgressPcntl();
////接下去的代码进入后台运行
//$process->runBackground();
//
//$process->run(function () {
//    echo "111\n";
//});
//$process->run(function () {
//    echo "111\n";
//});
//$process->run(function () {
//    echo "111\n";
//    sleep(10);
//});

//$process->run(function() use ($a) {
//    $b = 5;
//    while($b --) {
//        $a->sendMessage(new \Library\Message\MessageText('sjdskl', $b));
//        sleep(1);
//    }
//    sleep(10);
//});

//$process->run(function() use ($a) {
//    $b = new \Library\Tasks\TestTask($a);
//    $b->run();
//});



//$message = new Library\Message\MessageText('sjdjoke', '呵呵呵1');
//$a->sendMessage($message);
//$message->setMessage('我在发一条1');
//$a->sendMessage($message);
//$message_img = new \Library\Message\MessageImg('sjdjoke', '22.jpg');
//$a->sendMessage($message_img);
//$message_file = new \Library\Message\MessageFile('sjdjoke', 'README.md');
//$a->sendMessage($message_file);

$process = new Library\Progress\ProgressPcntl();
//接下去的代码进入后台运行
$process->runBackground();
$process->run(function() use ($a) {
    $a->listenMessage();
}, 'swxbot-listen-message');

$process->run(function() use ($a) {
    $b = new Library\Tasks\LogTask($a);
    $b->run(array(
        'php-act-root-dir' => '/home/vagrant/s-framework',
        'sleep_time' => 5,
        'monitor_files' => array('sql_error')
    ));
}, 'swxbot-log-task');

$process->wait();
print_r($process->getProgresses());



