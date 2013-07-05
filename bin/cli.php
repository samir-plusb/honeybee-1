<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_startup_errors', 'On');

$default_context = 'console';
$environment_suffix = '';

$rootDir = dirname(dirname(__FILE__));
require  $rootDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php';

AgaviContext::getInstance()->getController()->dispatch();
