<?php

/**
 * messagelink created at 2017-4-14 16:04:20
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

$link = new swxbot\Library\Message\MessageLink();
$link->setFilename('百度');
$link->setUrl('https://www.baidu.com');
$link->setNickname('sjdskl');
$a->sendMessage($link);
//
//$link = new swxbot\Library\Message\MessageText();
//$link->setMessage('123123');
//$link->setNickname('joke');
//$a->sendMessage($link);

//$link = new \swxbot\Library\Message\MessageImg();
//$link->setNickname('sjdskl');
//$link->setMedia("/tmp/1.jpg");
//$a->sendMessage($link);