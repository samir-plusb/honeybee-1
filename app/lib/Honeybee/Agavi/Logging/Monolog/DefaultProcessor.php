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
        $record['extra'] = array_merge(
            $record['extra'],
            array(
                'app_name' => \AgaviConfig::get('core.app_name'),
                'agavi_environment' => \AgaviConfig::get('core.environment'),
                'agavi_version' => \AgaviConfig::get('agavi.version'),
                'php_version' => phpversion(),
                'system' => php_uname(),
                'pid' => getmypid(),
                'memory_usage' => self::formatBytes(memory_get_usage(true)),
                'memory_peak_usage' => self::formatBytes(memory_get_peak_usage(true))
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
