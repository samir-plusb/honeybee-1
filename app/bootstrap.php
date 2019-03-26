<?php

// +---------------------------------------------------------------------------+
// | Require and hence setup the agavi configuration.                          |
// +---------------------------------------------------------------------------+
$root_dir = dirname(dirname(__FILE__));
require $root_dir . str_replace('/', DIRECTORY_SEPARATOR, '/app/config.php');

// +---------------------------------------------------------------------------+
// | Register our dat0r domain packages to the autoloader.                     |
// +---------------------------------------------------------------------------+
$dat0r_autoloading = $root_dir . str_replace('/', DIRECTORY_SEPARATOR,
    '/app/config/includes/autoload.php'
);
if (is_readable($dat0r_autoloading))
{
    require $dat0r_autoloading;
}

// make generated files group writeable for easy switch between web/console
umask(02);

// load environment
Honeybee\Core\Environment::load(false, $environment_modifier);
AgaviConfig::set('core.clean_environment', Honeybee\Core\Environment::getCleanEnvironment());

// +---------------------------------------------------------------------------+
// | Initialize the framework. You may pass an environment name to this method.|
// | By default the 'development' environment sets Agavi into a debug mode.    |
// | In debug mode among other things the cache is cleaned on every request.   |
// +---------------------------------------------------------------------------+

// @todo Atm this is needed to support routes that rely on the $_SERVER var for their source attribute.
$_SERVER['AGAVI_ENVIRONMENT'] = Honeybee\Core\Environment::toEnvString();
Agavi::bootstrap($_SERVER['AGAVI_ENVIRONMENT']);
AgaviConfig::set('core.default_context', $default_context);