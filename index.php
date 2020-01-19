<?php
if (version_compare(PHP_VERSION, '5.3.0', '<')) {
    die('require PHP > 5.3.0 !');
}

// THINK_PATH    框架目录
// STORAGE_TYPE    存储类型（默认为File）
// APP_MODE    应用模式（默认为common）

define('BUILD_DIR_SECURE', false); # don't generate index.html
define('APP_DEBUG', true); # debug mode
define('APP_PATH', './Application/'); # apps dir, only one
define('RUNTIME_PATH', '/tmp/tp-runtime/'); # runtime dir, need writable

require './ThinkPHP/ThinkPHP.php';
