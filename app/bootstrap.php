<?php

// +---------------------------------------------------------------------------+
// | Require and hence setup the agavi configuration.                          |
// +---------------------------------------------------------------------------+
$rootDir = dirname(dirname(__FILE__));
require $rootDir . '/app/config.php';
umask(02); // make generated files group writeable for easy switch between web/console

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
