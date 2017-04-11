swxbot a weixin clien
=======================

Guzzle is php weixin client that can login and send message and do something 
you wang

- Sample login weixin
- multi process

```
#example

namespace swxbot;
require APP_PATH . '/vendor/autoload.php';
$a = new Core\WeChat(Library\Login\Logininfo::getInstance());
$a->showQrCode();
$a->login();
//send message
$message = new Library\Message\MessageText('sjdjoke', '呵呵呵1');
$a->sendMessage($message);
$message->setMessage('我在发一条1');
$a->sendMessage($message);
$message_img = new \Library\Message\MessageImg('sjdjoke', '22.jpg');
$a->sendMessage($message_img);
$message_file = new \Library\Message\MessageFile('sjdjoke', 'README.md');
$a->sendMessage($message_file);

//mulit process task
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

```

