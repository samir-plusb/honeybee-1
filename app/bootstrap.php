<?php

// +---------------------------------------------------------------------------+
// | Require and hence setup the agavi configuration.                          |
// +---------------------------------------------------------------------------+
$rootDir = dirname(dirname(__FILE__));
require $rootDir . str_replace('/', DIRECTORY_SEPARATOR, '/app/config.php');

// +---------------------------------------------------------------------------+
// | Register our dat0r domain packages to the autoloader.                     |
// +---------------------------------------------------------------------------+
$dat0rAutoloading = $rootDir . str_replace('/', DIRECTORY_SEPARATOR,
    '/app/config/includes/autoload.php'
);
if (is_readable($dat0rAutoloading))
{
    require $dat0rAutoloading;
}

// make generated files group writeable for easy switch between web/console
umask(02);

// +---------------------------------------------------------------------------+
// | An absolute filesystem path to our environment config provider.           |
// +---------------------------------------------------------------------------+
require $rootDir . '/app/lib/config/ProjectEnvironmentConfig.class.php';
ProjectEnvironmentConfig::load(FALSE);

// +---------------------------------------------------------------------------+
// | Initialize the framework. You may pass an environment name to this method.|
// | By default the 'development' environment sets Agavi into a debug mode.    |
// | In debug mode among other things the cache is cleaned on every request.   |
// +---------------------------------------------------------------------------+

// @todo Atm this is needed to support routes that rely on the $_SERVER var for their source attribute.
$_SERVER['AGAVI_ENVIRONMENT'] = ProjectEnvironmentConfig::toEnvString();
Agavi::bootstrap($_SERVER['AGAVI_ENVIRONMENT']);
AgaviConfig::set('core.default_context', $default_context);
