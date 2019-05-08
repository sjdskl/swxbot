swxbot a weixin client
=======================

Guzzle is php weixin client that can login and send message and do something 
you want

- Sample login weixin
```
#create and add logininfo
$swxbot = new swxbot\Core\WeChat(swxbot\Library\Login\Logininfo::getInstance());
#show qrcode
$swxbot->showQrCode();
#login
$swxbot->login();
```
- multi process
```
#create process
$process = new swxbot\Library\Progress\ProgressPcntl();
#run background
$process->runBackground();
#create new process to run your code
$process->run(function () use ($swxbot) {
    echo "i am sjdskl1\n";
}, 'swxbot-process1');
$process->run(function () use ($swxbot) {
    echo "i am sjdskl2\n";
}, 'swxbot-process2');
```
- tasks
```
#use process run tasks, your task need extends abstractTasks
$process->runTask(new \Library\Tasks\TzgLog($swxbot), 
array(
    'php-act-root-dir' => '/opt/web-data/php-act',
    'sleep_time' => 5,
    'monitor_files' => array('sql_error')
), 
'swxbot-log-notify');
```
- listen message helper
```
#you can user helper to handle the message
$helper = new swxbot\Library\Helper\ListenMessageHelper();
$helper->add('test', new swxbot\Library\ListenMessage\TestListenMessage());
$helper->add('test2', new swxbot\Library\ListenMessage\TestListenMessage2());
$swxbot->setListenHelper($helper);
$process->runBackground();
$process->run(function () use ($swxbot) {
    $swxbot->listenMessage();
}, 'swxbot-listen-message');
```