<?php

namespace Honeybee\Agavi\Logging;

use Monolog\Logger;

/**
 * Sends AgaviLoggerMessages to a \Monolog\Logger instance.
 *
 * The following parameters are supported:
 *
 * setup - Honeybee\Agavi\Logging\Monolog\IMonologSetup implementing class name
 *         to instantiate to get a configured \Monolog\Logger with wanted
 *         channel name, handlers, processors and formatters.
 */
class MonologLoggerAppender extends \AgaviLoggerAppender
{
    /**
     * @var logger \Monolog\Logger instance
     */
    protected $logger = array();

    /**
     * Retrieve the Monolog instance to write to.
     *
     * @return \Monolog\Logger instance to use for logging
     */
    protected function getMonologInstance()
    {
        if (!$this->logger)
        {
            $setup = $this->getParameter('setup', 'Honeybee\\Agavi\\Logging\\Monolog\\DefaultSetup');
            if (class_exists($setup))
            {
                $this->logger = $setup::getMonologInstance($this);
            }
            else
            {
                throw new \AgaviLoggingException("The class '$setup' configured via parameter 'setup' doesn't exist. Please check spelling or autoloading.");
            }
        }

        return $this->logger;
    }

    /**
     * Write log data to this appender.
     *
     * @param \AgaviLoggerMessage $message log data to be written
     *
     * @throws \AgaviLoggingException if no layout is set or the stream can't be written
     */
    public function write(\AgaviLoggerMessage $message)
    {
        if(($layout = $this->getLayout()) === null)
        {
            throw new \AgaviLoggingException('No Layout set for logging.');
        }

        $monolog_level = $this->convertAgaviLevelToMonologLevel($message->getLevel());
        $monolog_message = (string)$this->getLayout()->format($message);
        $monolog_context = $message->getParameter('psr3.context', array());

        $this->getMonologInstance()->log($monolog_level, $monolog_message, $monolog_context);
    }

    /**
     * @param int $log_level_or_severity One of \AgaviLogger::DEBUG etc.
     *
     * @return int one of \Monolog\Logger log levels (defaults to \Monolog\Logger::INFO if nothing matches exactly)
     */
    public function convertAgaviLevelToMonologLevel($log_level_or_severity)
    {
        if (!is_int($log_level_or_severity))
        {
            throw new \InvalidArgumentException("The given log level '$log_level_or_severity' is not an integer. Please use AgaviLogger::DEBUG or similar.");
        }

        $log_level_or_severity = abs($log_level_or_severity);

        // patched Agavi log level => Monolog log level
        $levels = array(
            \AgaviLogger::TRACE => Logger::DEBUG,
            \AgaviLogger::DEBUG => Logger::DEBUG,
            \AgaviLogger::INFO => Logger::INFO,
            \AgaviLogger::NOTICE => Logger::NOTICE,
            \AgaviLogger::WARN => Logger::WARNING,
            \AgaviLogger::WARNING => Logger::WARNING,
            \AgaviLogger::ERROR => Logger::ERROR,
            \AgaviLogger::FATAL => Logger::CRITICAL,
            \AgaviLogger::CRITICAL => Logger::CRITICAL,
            \AgaviLogger::ALERT => Logger::ALERT,
            \AgaviLogger::EMERGENCY => Logger::EMERGENCY
        );

        $level = Logger::INFO; // default

        if (isset($levels[$log_level_or_severity]))
        {
            $level = $levels[$log_level_or_severity];
        }
        else
        {
            // default (unpatched) Agavi versions <= v1.0.7
            if ($log_level_or_severity > \AgaviLogger::FATAL && $log_level_or_severity <= \AgaviLogger::ALL)
            {
                $level = Logger::ALERT; // integer values above FATAL are ALERTs for us
            }
            else if ($log_level_or_severity > \AgaviLogger::INFO && $log_level_or_severity < \AgaviLogger::WARN)
            {
                $level = Logger::NOTICE; // integer values between INFO and WARNING are NOTICEs for us
            }
            else
            {
                // EMERGENCY level or similar are not mapped and fall back to the default level set
            }
        }

        return $level;
    }

    /**
     * Execute the shutdown procedure.
     */
    public function shutdown()
    {
        // nothing to do here as Monolog doesn't seem to have a shutdown method
        // and the handlers implement a close() method triggered by __destruct()
        // calls or register a shutdown function themselves
    }
}
