<?php
//phpinfo();
//
//exit();
//$ppid = posix_getpid();
//
//echo "master process $ppid running\n";
//
//for ( $i = 0; $i < 2; $i++ ){
//    $pid = pcntl_fork();
//    switch ($pid) {
//
//        case '-1':
//            echo "fork error";
//            break;
//
//        case '0':
//            $child_pid = posix_getpid();
//            cli_set_process_title("php-worker");
//            $sec = rand(1,10);
//            echo "worker $child_pid start sleep $sec seconds\n";
//            sleep($sec);
//            echo "worker $child_pid exit\n";
//            exit();
//            break;
//    }
//}
//
//cli_set_process_title("php master process");
//$ret = pcntl_wait($status);
//
//echo "ret: $ret , status $status \n";
//echo "master process exit";
//
// THINK_PATH    框架目录
// STORAGE_TYPE  存储类型（默认为File）
// APP_MODE      应用模式（默认为common）

define('BUILD_DIR_SECURE', false); # don't generate index.html
define('APP_DEBUG', true); # debug mode
define('APP_PATH', './Application/'); # apps dir, only one
define('RUNTIME_PATH', '/tmp/tp-runtime/'); # runtime dir, need writable

require './ThinkPHP/ThinkPHP.php';
