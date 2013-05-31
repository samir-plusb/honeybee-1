<?php

namespace Honeybee\Agavi\Logging;

use \Psr\Log\LoggerInterface;
use \Psr\Log\LogLevel;

/**
 * PSR-3 compatibile logger instance that wraps an \AgaviLogger.
 */
class Psr3Logger implements LoggerInterface
{
    /**
     * @var \AgaviLogger instance used for logging
     */
    protected $logger;

    /**
     * @param \AgaviILogger $logger instance to use for logging
     */
    public function __construct(\AgaviILogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return \AgaviLogger instance used for logging
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param \AgaviILogger $logger instance to replace the currently used instance
     *
     * @return void
     */
    public function setLogger(\AgaviILogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Log a message at DEBUG level.
     *
     * The message may contain placeholders like "{foo}" where foo
     * will be replaced by the context data in key "foo".
     *
     * @param mixed $message string or object implementing __toString()
     * @param array $context arbitrary data
     *
     * @return void
     */
    public function debug($message, array $context = array())
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * Log a message at INFO level.
     *
     * The message may contain placeholders like "{foo}" where foo
     * will be replaced by the context data in key "foo".
     *
     * @param mixed $message string or object implementing __toString()
     * @param array $context arbitrary data
     *
     * @return void
     */
    public function info($message, array $context = array())
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * Log a message at NOTICE level.
     *
     * The message may contain placeholders like "{foo}" where foo
     * will be replaced by the context data in key "foo".
     *
     * @param mixed $message string or object implementing __toString()
     * @param array $context arbitrary data
     *
     * @return void
     */
    public function notice($message, array $context = array())
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * Log a message at WARNING level.
     *
     * The message may contain placeholders like "{foo}" where foo
     * will be replaced by the context data in key "foo".
     *
     * @param mixed $message string or object implementing __toString()
     * @param array $context arbitrary data
     *
     * @return void
     */
    public function warning($message, array $context = array())
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * Log a message at ERROR level.
     *
     * The message may contain placeholders like "{foo}" where foo
     * will be replaced by the context data in key "foo".
     *
     * @param mixed $message string or object implementing __toString()
     * @param array $context arbitrary data
     *
     * @return void
     */
    public function error($message, array $context = array())
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * Log a message at CRITICAL level.
     *
     * The message may contain placeholders like "{foo}" where foo
     * will be replaced by the context data in key "foo".
     *
     * @param mixed $message string or object implementing __toString()
     * @param array $context arbitrary data
     *
     * @return void
     */
    public function critical($message, array $context = array())
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * Log a message at ALERT level.
     *
     * The message may contain placeholders like "{foo}" where foo
     * will be replaced by the context data in key "foo".
     *
     * @param mixed $message string or object implementing __toString()
     * @param array $context arbitrary data
     *
     * @return void
     */
    public function alert($message, array $context = array())
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * Log a message at EMERGENCY level.
     *
     * The message may contain placeholders like "{foo}" where foo
     * will be replaced by the context data in key "foo".
     *
     * @param mixed $message string or object implementing __toString()
     * @param array $context arbitrary data
     *
     * @return void
     */
    public function emergency($message, array $context = array())
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * Logs a message with a given log level by mapping the given parameters to
     * \AgaviLogger compatible things. Does not log anything if the setting
     * 'core.use_logging' is disabled.
     *
     * @param mixed $level PSR-3 log level or an \AgaviLoggerMessage instance
     * @param mixed $message string or object implementing __toString(); unused when an \AgaviLoggerMessage is given as $level
     * @param array $context arbitrary data to use for templated message; unused when an \AgaviLoggerMessage is given as $level
     *
     * @return void
     */
    public function log($level, $message, array $context = array())
    {
        if (!\AgaviConfig::get('core.use_logging', true))
        {
            return;
        }

        $logger_message = $level;

        // construct an \AgaviLoggerMessage instance from PSR-3 parameters
        if (!$logger_message instanceof \AgaviLoggerMessage)
        {
            $agavi_context = \AgaviContext::getInstance();

            /* @var $logger_manager Honeybee\Agavi\Logging\LoggerManager */
            $logger_manager = $agavi_context->getLoggerManager();

            $message = self::replacePlaceholders($message, $context);

            $level = $this->logger->mapPsr3LogLevelToAgavi($level, \AgaviLogger::WARNING);

            $class_name = $logger_manager->getDefaultMessageClass();

            /* @var $logger_message \AgaviLoggerMessage */
            $logger_message = new $class_name();
            $logger_message->setLevel($level);
            $logger_message->setMessage($message);
        }

        $this->logger->log($logger_message);
    }

    /**
     * Replace placeholders (keys) with values in the given message.
     *
     * @param string $message log message with placeholders
     * @param array $context associative array of key => value pairs to use for replacement
     *
     * @return string message with replaced values from context
     */
    public static function replacePlaceholders($message, array $context = array ())
    {
        if (is_array($message))
        {
            return $message;
        }

        $replacements = array ();

        // build the replacement array with braces around the context keys
        foreach ($context as $key => $value)
        {
            if (is_object($value) && get_class($value) === 'DateTime')
            {
                $value = $value->format('c'); // ISO 8601 date
            }
            elseif (is_object($value))
            {
                $value = json_encode($value);
            }
            elseif (is_array($value))
            {
                $value = json_encode($value);
            }
            elseif (is_resource($value))
            {
                $value = (string)$value;
            }

            $replacements['{' . $key . '}'] = $value;
        }

        // replace placeholders and return the message
        return strtr($message, $replacements);
    }
}
