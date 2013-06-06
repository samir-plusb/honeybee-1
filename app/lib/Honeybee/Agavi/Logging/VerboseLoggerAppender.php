<?php

namespace Honeybee\Agavi\Logging;

use Honeybee\Agavi\Logging\FileLoggerAppender;

/**
 * Extends the FileLoggerAppender message with various system, Agavi and
 * application information that may be helpful with debugging.
 */
class VerboseLoggerAppender extends FileLoggerAppender
{
    /**
     * Adds various system, Agavi and application specific debugging
     * information to the given logger message as a json string.
     *
     * @param \AgaviLoggerMessage $message
     *
     * @return void
     */
    public function write(\AgaviLoggerMessage $message)
    {
        $message_text = $message->getMessage();

        $agavi_context = \AgaviContext::getInstance();

        $request_uri = '';
        if (php_sapi_name() !== 'cli' && isset($_SERVER['REQUEST_URI']))
        {
            $request_uri = $_SERVER['REQUEST_URI'];
        }
        else
        {
            $request_uri = $agavi_context->getRouting()->getInput();
        }

        $matched_module_and_action = '';
        $matched_routes = '';
        $route_names_array = $agavi_context->getRequest()->getAttribute('matched_routes', 'org.agavi.routing');
        if (!empty($route_names_array))
        {
            $main_route = $agavi_context->getRouting()->getRoute(reset($route_names_array));
            $matched_module_and_action = $main_route['opt']['module'] . '/' . $main_route['opt']['action'];
            $matched_routes = implode(', ', $route_names_array);
        }

        $data = array(
            'app_name' => \AgaviConfig::get('core.app_name'),
            'agavi_context' => $agavi_context->getName(),
            'agavi_environment' => \AgaviConfig::get('core.environment'),
            'agavi_version' => \AgaviConfig::get('agavi.version'),
            'php_version' => phpversion(),
            'system' => php_uname(),
            'pid' => getmypid(),
            'memory_usage' => self::formatBytes(memory_get_usage(true)),
            'memory_peak_usage' => self::formatBytes(memory_get_peak_usage(true)),
            'remote_addr' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'console',
            'x_forwarded_for' => isset($_SERVER['X_FORWARDED_FOR']) ? $_SERVER['X_FORWARDED_FOR'] : '',
            'request_uri' => $request_uri,
            'request_method' => $agavi_context->getRequest()->getMethod(),
            'matched_module_and_action' => $matched_module_and_action,
            'matched_routes' => $matched_routes,
            'raw_user_agent' => $agavi_context->getUser()->getRawUserAgent(),
            'raw_referer' => $agavi_context->getUser()->getRawReferer()
        );

        $message->setMessage($message_text . ' extra=' . json_encode($data));

        parent::write($message);
    }

    /**
     * Formats bytes into a human readable string.
     *
     * @param int $bytes
     *
     * @return string
     */
    protected static function formatBytes($bytes)
    {
        $bytes = (int) $bytes;

        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');

        return round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), 3) . ' ' . $units[$i];
    }
}
