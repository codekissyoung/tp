<?php
echo "hello";

// --------------------------- ThinkPHP ----------------------------------
define('APP_DEBUG', true); // debug mode
define('APP_PATH', './Application/'); // apps dir, only one
define('RUNTIME_PATH', '/tmp/tp-runtime/'); // runtime dir, need writable
require './ThinkPHP/ThinkPHP.php';
