<?php
echo "hello";

session_start();

define('APP_PATH', './Application/');
define('RUNTIME_PATH', '/tmp/tp-runtime/');
require './ThinkPHP/ThinkPHP.php';
