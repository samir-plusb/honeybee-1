<?php

namespace Honeybee\Agavi\Logging;

use \Psr\Log\LogLevel;

/**
 * Extends \AgaviLogger with some convenience methods and
 * the possibility to get a PSR-3 compatible logger.
 */
class Logger extends \AgaviLogger
{
    /**
     * @var array associative array of supported Agavi log levels.
     */
    protected static $levels = array(
        256 => 'TRACE',
        128 => 'DEBUG',
         64 => 'INFO',
         32 => 'NOTICE',
         16 => 'WARNING',
          8 => 'ERROR',
          4 => 'CRITICAL',
          2 => 'ALERT',
          1 => 'EMERGENCY'
    );

    /**
     * @var array mapping from PSR-3 defined log levels to Agavi log levels.
     */
    protected static $psr3_to_agavi_level_mapping = array(
        // LogLevel::TRACE => \AgaviLogger::TRACE,
        LogLevel::DEBUG     => \AgaviLogger::DEBUG,
        LogLevel::INFO      => \AgaviLogger::INFO,
        LogLevel::NOTICE    => \AgaviLogger::NOTICE,
        LogLevel::WARNING   => \AgaviLogger::WARNING,
        LogLevel::ERROR     => \AgaviLogger::ERROR,
        LogLevel::CRITICAL  => \AgaviLogger::CRITICAL,
        LogLevel::ALERT     => \AgaviLogger::ALERT,
        LogLevel::EMERGENCY => \AgaviLogger::EMERGENCY
    );

    /**
     * Gets all supported logging levels.
     *
     * @return array associative array with human-readable Agavi log level names => log level codes.
     */
    public static function getLevels()
    {
        return array_flip(static::$levels);
    }

    /**
     * Returns the name of the given Agavi log level.
     *
     * @param int $level Agavi log level severity
     * 
     * @return string name of given severity value
     */
    public static function getLevelName($level)
    {
        if (!isset(static::$levels[$level]))
        {
            throw new \InvalidArgumentException("Log level '$level' is undefined, use one of: " . implode(', ', array_keys(static::$levels)));
        }

        return static::$levels[$level];
    }

    /**
     * @return \Honeybee\Agavi\Logging\Psr3Logger instance that is compatible to the PSR-3 standard
     */
    public function getPsr3Logger()
    {
        return new Psr3Logger($this);
    }

    /**
     * @param string $level PSR-3 log level like \Psr\Log\LogLevel::DEBUG
     * @param int $default default Agavi log level to use when mapping fails or level is unknown
     * 
     * @return int Agavi log level or given default
     */
    public function mapPsr3LogLevelToAgavi($level, $default = \AgaviLogger::INFO)
    {
        if (is_string($level) && isset(self::$psr3_to_agavi_level_mapping[$level]))
        {
            return self::$psr3_to_agavi_level_mapping[$level]; // map PSR-3 log level to Agavi log level
        }
        elseif (isset(self::$levels[$level]))
        {
            return $level; // seems to be an Agavi log level so we use that
        }
        else
        {
            return $default; // default level as it's no known Agavi log level
        }
    }
}
