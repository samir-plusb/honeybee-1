<?php

ini_set('xdebug.max_nesting_level', '200');

$default_context = 'web';
$environment_modifier = '';

$rootDir = dirname(__DIR__);
require $rootDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php';

if (FALSE !== strpos(\Honeybee\Core\Environment::getEnvironment(), 'development'))
{
    PhpDebugToolbar::start(array(
        'js_location' => 'static/PhpDebugToolbar/PhpDebugToolbar.js',
        'css_location' => 'static/PhpDebugToolbar/PhpDebugToolbar.css'
    ));
}

AgaviContext::getInstance()->getController()->dispatch();
