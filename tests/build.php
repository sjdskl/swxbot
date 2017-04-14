<?php

/**
 * build created at 2017-3-23 15:45:49
 * The encoding is UTF-8
 * 
 * @author skl@tzg.cn
 */

$dir = __DIR__;             // 需要打包的目录
$file = 'swxbot.phar';      // 包的名称, 注意它不仅仅是一个文件名, 在stub中也会作为入口前缀
$phar = new Phar(__DIR__ . '/' . $file, FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME, $file);
// 开始打包
$phar->startBuffering();
$phar->buildFromDirectory($dir);
$phar->delete('build.php');
$phar->addFile('vendor/rmccue/requests/library/Requests/Transport/cacert.pem');
// 设置入口   
$phar->setStub("#!/usr/bin/env php
<?php
Phar::mapPhar('{$file}');
require 'phar://{$file}/index.php';
__HALT_COMPILER();
?>");
$phar->stopBuffering();
// 打包完成
echo "Finished {$file}\n";