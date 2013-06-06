<?php

namespace Honeybee\Agavi\Logging\Monolog;

/**
 * Default processor for Monolog log messages that adds Agavi, system and
 * application specific information to the extra field of the log record.
 */
class DefaultProcessor
{
    /**
     * @param array $record Monolog log record with message, context, extra
     *
     * @return array with additional extra information
     */
    public function __invoke(array $record)
    {
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

        $record['extra'] = array_merge(
            $record['extra'],
            array(
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
            )
        );

        return $record;
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
