<?php
// +---------------------------------------------------------------------------+
// | Initialize some common directory vars and set our include path.           |
// +---------------------------------------------------------------------------+
$root_dir = dirname(dirname(__FILE__));
$libs_dir = $root_dir . DIRECTORY_SEPARATOR . 'libs';

$includes = array($libs_dir);
set_include_path(implode(PATH_SEPARATOR, $includes).PATH_SEPARATOR.get_include_path());

// +---------------------------------------------------------------------------+
// | An absolute filesystem path to our environment config provider.           |
// +---------------------------------------------------------------------------+
require $root_dir . DIRECTORY_SEPARATOR . 'app/lib/config/ProjectEnvironmentConfig.class.php';

// +---------------------------------------------------------------------------+
// | An absolute filesystem path to the agavi/agavi.php script.                |
// +---------------------------------------------------------------------------+
require 'agavi/agavi.php';

// +---------------------------------------------------------------------------+
// | An absolute filesystem path to our app/config.php script.                 |
// +---------------------------------------------------------------------------+
require $root_dir . DIRECTORY_SEPARATOR . 'app/config.php';

// +---------------------------------------------------------------------------+
// | PhpDebugToolbar iintegration                                              |
// +---------------------------------------------------------------------------+
if (!strstr(ProjectEnvironmentConfig::getEnvironment(), 'live')) // @todo Think about this, is the right include condition?
{
    $debugbar_dir = $libs_dir . DIRECTORY_SEPARATOR . 'PhpDebugToolbar' . DIRECTORY_SEPARATOR;
    require $debugbar_dir . 'PhpDebugToolbar.class.php';

    PhpDebugToolbar::start(array(
        'js_location'  => 'debugbar/js/PhpDebugToolbar.js',
        'css_location' => 'debugbar/css/PhpDebugToolbar.css'
    ));
}

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
if (preg_match('/\/binaries/i', $_SERVER['QUERY_STRING']))
{
    AgaviContext::getInstance('web_binaries')->getController()->dispatch();
}
else
{
    AgaviContext::getInstance('web')->getController()->dispatch();
}
