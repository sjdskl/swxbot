<?php

/**
 * Tuling created at 2017-4-13 15:43:55
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

$b = new swxbot\Library\ListenMessage\TulingMessage();
$b->run(array(
    'ToUserName' => 'me_donkey',
    'Content' => '小鸟的图片',
), $a);


//$b = new swxbot\Library\ListenMessage\TulingMessage();
//$b->run(array(
//    'ToUserName' => 'me_donkey',
//    'Content' => '小狗狗的图片',
//), new swxbot\Core\WeChat(swxbot\Library\Login\Logininfo::getInstance()));
