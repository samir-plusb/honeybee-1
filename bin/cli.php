<?php

$default_context = 'console';

$rootDir = dirname(dirname(__FILE__));
require  $rootDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'dispatch.php';

AgaviContext::getInstance('console')->getController()->dispatch();
