<?php

$rootDir = dirname(dirname(__FILE__));
require $rootDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'dispatch.php';

// +---------------------------------------------------------------------------+
// | PhpDebugToolbar integration                                               |
// +---------------------------------------------------------------------------+
//
// @todo Think about this, is the right include condition?
if (! strstr(ProjectEnvironmentConfig::getEnvironment(), 'live'))
{
    PhpDebugToolbar::start(array(
        'js_location' => 'static/PhpDebugToolbar/PhpDebugToolbar.js',
        'css_location' => 'static/PhpDebugToolbar/PhpDebugToolbar.css'
    ));
}

AgaviConfig::set(
    'core.default_context',
    preg_match('/\/binaries/i', $_SERVER['QUERY_STRING'])
        ? 'web_binaries'
        : 'web'
);

AgaviContext::getInstance()->getController()->dispatch();
