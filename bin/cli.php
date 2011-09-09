<?php
// +---------------------------------------------------------------------------+
// | Require our dispatch script, that takes care loading libs and environment.|
// +---------------------------------------------------------------------------+
$testingEnabled = false;
$rootDir = dirname(dirname(__FILE__));
require  $rootDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'dispatch.php';

// +---------------------------------------------------------------------------+
// | An absolute filesystem path to the agavi/agavi.php script.                |
// +---------------------------------------------------------------------------+
require 'agavi/agavi.php';

// +---------------------------------------------------------------------------+
// | An absolute filesystem path to our app/config.php script.                 |
// +---------------------------------------------------------------------------+
require $rootDir . DIRECTORY_SEPARATOR . 'app/config.php';
AgaviConfig::set('core.default_context', 'console');

// +---------------------------------------------------------------------------+
// | Initialize the framework. You may pass an environment name to this method.|
// | By default the 'development' environment sets Agavi into a debug mode.    |
// | In debug mode among other things the cache is cleaned on every request.   |
// +---------------------------------------------------------------------------+

// @todo Atm this is needed to support routes that rely on the $_SERVER var for their source attribute.
$_SERVER['AGAVI_ENVIRONMENT'] = ProjectEnvironmentConfig::toEnvString();

Agavi::bootstrap(ProjectEnvironmentConfig::toEnvString());

// +---------------------------------------------------------------------------+
// | Call the controller's dispatch method on the default context              |
// +---------------------------------------------------------------------------+
AgaviContext::getInstance('console')->getController()->dispatch();

?>