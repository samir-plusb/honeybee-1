<?php

$default_context = // @todo do we really need/want this stunt?
    preg_match('/\/binaries/i', $_SERVER['QUERY_STRING'])
    ? 'web_binaries'
    : 'web';

$rootDir = dirname(dirname(__FILE__));
require $rootDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'dispatch.php';

if (! strstr(ProjectEnvironmentConfig::getEnvironment(), 'live'))
{
    PhpDebugToolbar::start(array(
        'js_location' => 'static/PhpDebugToolbar/PhpDebugToolbar.js',
        'css_location' => 'static/PhpDebugToolbar/PhpDebugToolbar.css'
    ));
}

AgaviContext::getInstance()->getController()->dispatch();
