<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_startup_errors', 'On');

$default_context = 'console';
$environment_modifier = '';

$root_dir = dirname(dirname(__FILE__));
require  $root_dir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php';
unset($root_dir);

$response = AgaviContext::getInstance()->getController()->dispatch();
exit($response->getExitCode());
