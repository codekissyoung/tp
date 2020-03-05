<?php
echo "hello";

// --------------------------- ThinkPHP ----------------------------------
define('APP_DEBUG', true);
define('APP_PATH', './Application/');
define('RUNTIME_PATH', '/tmp/tp-runtime/');
require './ThinkPHP/ThinkPHP.php';
