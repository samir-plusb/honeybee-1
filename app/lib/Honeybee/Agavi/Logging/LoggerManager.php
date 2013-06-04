<?php

namespace Honeybee\Agavi\Logging;

use Honeybee\Core\Dat0r\Module;
use Honeybee\Core\Dat0r\Document;
use Honeybee\Agavi\Logging\Logger;
use Honeybee\Agavi\Logging\Psr3Logger;

/**
 * Extends \AgaviLoggerManager with log level specific convenience methods.
 */
class LoggerManager extends \AgaviLoggerManager implements ILogger//, \Psr\Log\LoggerAwareInterface
{
    private static $pid;

    /**
     * @var string to use as default scope in log message
     */
    const DEFAULT_MESSAGE_SCOPE = 'Honeybee';

    public function logTrace()
    {
        $this->log($this->createLoggerMessage(\AgaviLogger::TRACE, self::DEFAULT_MESSAGE_SCOPE, func_get_args()));
    }

    public function logDebug()
    {
        $this->log($this->createLoggerMessage(\AgaviLogger::DEBUG, self::DEFAULT_MESSAGE_SCOPE, func_get_args()));
    }

    public function logInfo()
    {
        $this->log($this->createLoggerMessage(\AgaviLogger::INFO, self::DEFAULT_MESSAGE_SCOPE, func_get_args()));
    }

    public function logNotice()
    {
        $this->log($this->createLoggerMessage(\AgaviLogger::NOTICE, self::DEFAULT_MESSAGE_SCOPE, func_get_args()));
    }

    public function logWarning()
    {
        $this->log($this->createLoggerMessage(\AgaviLogger::WARNING, self::DEFAULT_MESSAGE_SCOPE, func_get_args()));
    }

    public function logError()
    {
        $this->log($this->createLoggerMessage(\AgaviLogger::ERROR, self::DEFAULT_MESSAGE_SCOPE, func_get_args()));
    }

    public function logCritical()
    {
        $this->log($this->createLoggerMessage(\AgaviLogger::CRITICAL, self::DEFAULT_MESSAGE_SCOPE, func_get_args()));
    }

    public function logAlert()
    {
        $this->log($this->createLoggerMessage(\AgaviLogger::ALERT, self::DEFAULT_MESSAGE_SCOPE, func_get_args()));
    }

    public function logEmergency()
    {
        $this->log($this->createLoggerMessage(\AgaviLogger::EMERGENCY, self::DEFAULT_MESSAGE_SCOPE, func_get_args()));
    }

    /**
     * Creates and returns an \AgaviLoggerMessage instance. To return a
     * different class set the default_message_class parameter in the
     * for the logger_manager entry in the factories.xml file.
     *
     * The log message parts need to be either strings, arrays or objects
     * implementing __toString(). Instances of the following classes are
     * treated in a special way automatically:
     * - \Exception
     * - Honeybee\Core\Dat0r\Module - name of module
     * - Honeybee\Core\Dat0r\Document - uuid of document
     * - \AgaviValidationManager - messages of all incidents
     * - \DateTime - ISO-8601 representation
     *
     * @see self::getAsString()
     *
     * @param int $log_level Agavi log level to use
     * @param string $scope name for the scope to use
     * @param array $log_message_parts array of strings, arrays and objects that should be part of the message created
     *
     * @return \AgaviLoggerMessage
     *
     * @throws \InvalidArgumentException if __toString() is not callable on a log message part object
     */
    public function createLoggerMessage($log_level, $scope, array $log_message_parts = array())
    {
        $text_message_parts = array();
        $class_name = $this->getDefaultMessageClass();
        $logger_message = new $class_name();
        $logger_message->setLevel($log_level);
        $logger_message->setParameter('scope', trim($scope));

        if (2 === count($log_message_parts) && is_string($log_message_parts[0]) && self::isAssoc($log_message_parts[1]) && (false !== strpos($log_message_parts[0], '{')))
        {
            // might be a PSR-3 compatible log call with templated message and context array
            $logger_message->setParameter('psr3.context', $log_message_parts[1]);
            $logger_message->setLevel(Logger::getAgaviLogLevel($log_level));
            $logger_message->setMessage(Psr3Logger::replacePlaceholders($log_message_parts[0], $log_message_parts[1]));
            if (isset($log_message_parts[1]['scope']))
            {
                $logger_message->setParameter('scope', $log_message_parts[1]['scope']);
            }
            return $logger_message;
        }

        // normal Agavi logging - analyse log_message_parts to get nicely formatted strings for known classes etc.
        foreach ($log_message_parts as $log_message_part)
        {
            $text_message_parts[] = self::getAsString($log_message_part);
        }

        $logger_message->setMessage(implode(' ', $text_message_parts));

        return $logger_message;
    }

    /**
     * Returns a string representation for the given argument. Specifically
     * handles known types like exceptions, ValidationManager instances or
     * Honeybee Module and Document instances.
     *
     * @param mixed $log_message_part object, array or string to create textual representation for
     *
     * @return string for the given log message part
     */
    public static function getAsString($log_message_part)
    {
        if ($log_message_part instanceof \Exception)
        {
            return self::getExceptionAsString($log_message_part);
        }
        elseif (is_object($log_message_part))
        {
            return self::getObjectAsString($log_message_part);
        }
        elseif (is_array($log_message_part))
        {
            return print_r($log_message_part, true);
        }
        elseif (is_resource($log_message_part))
        {
            return (string) $log_message_part;
        }

        return (string) $log_message_part;
    }

    /**
     * Returns a string with exception message enhanced by various information
     * like Agavi and PHP version, timestamp, request and routing information.
     *
     * @param \Exception $exception exception to create a log message string for
     *
     * @return string with exception message and further information
     */
    public static function getExceptionAsString(\Exception $exception)
    {
        $extra = array();

        $extra['Timestamp'] = \DateTime::createFromFormat('U.u', sprintf('%.6F', microtime(true)))->format('Y-m-d\TH:i:s.uP');
        $extra['Application Name'] = \AgaviConfig::get('core.app_name');
        $extra['Agavi Environment'] = \AgaviConfig::get('core.environment');
        $extra['Agavi Version'] = \AgaviConfig::get('agavi.version');
        $extra['PHP Version'] = phpversion();
        $extra['System'] = php_uname();
        $extra['Process ID'] = getmypid();
        $extra['Memory Usage'] = self::formatBytes(memory_get_usage(true));
        $extra['Memory Peak Usage'] = self::formatBytes(memory_get_peak_usage(true));

        $agavi_context = \AgaviContext::getInstance();
        if (null !== ($request = $agavi_context->getRequest()))
        {
            if ($request instanceof \AgaviWebRequest)
            {
                $extra['Request URL'] = $request->getUrl();
                $extra['Request Method'] = $request->getMethod();
            }
            elseif ($request instanceof \AgaviConsoleRequest)
            {
                $extra['Input'] = $request->getInput();
            }

            $matched_routes = $request->getAttribute('matched_routes', 'org.agavi.routing');

            if ($matched_routes)
            {
                $extra['Matched Routes (' . count($matched_routes) . ')'] = implode(', ', $matched_routes);
            }

            if (!$request->isLocked())
            {
                $extra['Parameter Names (Request)'] = implode(', ', array_keys($request->getRequestData()->getParameters()));
            }
        }

        foreach ($extra as $key => $value)
        {
            $message_parts[] = str_pad(' ', 30 - strlen($key)) . $key . ': ' . $value;
        }

        return (string) $exception . PHP_EOL . implode(PHP_EOL, $message_parts);
    }

    /**
     * Returns a string for the given object enhanced by various information if
     * the object is of a known type like \AgaviValidationManager, Honeybee
     * Module or Document. The given object should implement a `__toString()`
     * method as otherwise the json representation might be empty.
     *
     * @param mixed $obj object to create a log message string for
     *
     * @return string with object representation
     */
    public static function getObjectAsString($obj)
    {
        if ($obj instanceof Module)
        {
            return 'Module (Name=' . $obj->getName() . ')';
        }
        elseif ($obj instanceof Document)
        {
            return 'Document (Identifier=' . $obj->getIdentifier() . ')';
        }
        elseif ($obj instanceof \DateTime)
        {
            return $obj->format('c');
        }
        elseif ($obj instanceof \AgaviValidationManager)
        {
            $validation_messages = array();
            foreach ($obj->getErrorMessages() as $incident)
            {
                if (!empty($incident['message']))
                {
                    $validation_messages[] = $incident['message'];
                }
            }

            return 'Validation Errors (' . implode(', ', $validation_messages) . ')';
        }
        elseif (is_callable(array($obj, '__toString')))
        {
            return $obj->__toString();
        }
        else
        {
            return json_encode($obj);
        }
    }

    /**
     * Logs all given $args to the given logger with the specifed log level and scope name.
     *
     * @param string $logger_name log channel name (logger name defined in logging.xml)
     * @param int $log_level log level to use for logger message creation
     * @param string $scope string or object implementing __toString() for scope of log message (e.g. callee class name or sub channel name)
     * @param array $args arbitrary number of parameters to log (need to be of known type or implement __toString())
     *
     * @return void
     *
     * @throws \InvalidArgumentException when there's no logger configured for the given logger name
     */
    public static function logLoggerAndLevel($logger_name, $log_level, $scope, $args)
    {
        if (!\AgaviConfig::get('core.use_logging', true))
        {
            return;
        }

        /* @var $agavi_context \AgaviContext */
        $agavi_context = \AgaviContext::getInstance();

        /* @var $logger_manager Honeybee\Agavi\Logging\LoggerManager */
        $logger_manager = $agavi_context->getLoggerManager();

        /* @var $logger Honeybee\Agavi\Logging\Logger */
        $logger = $logger_manager->getLogger($logger_name);

        if (!$logger)
        {
            throw new \InvalidArgumentException("Can't find logger with name '$logger_name'. Please specify another name or define the logger in the logging.xml file.");
        }

        $logger_message = $logger_manager->createLoggerMessage($log_level, $scope, $args);

        $logger->log($logger_message);
    }

    /**
     * Logs all given $args to the given logger with the specifed log level and scope name.
     *
     * @param string $logger_name log channel name (logger name defined in logging.xml)
     * @param int $log_level log level to use for logger message creation
     * @param string $scope string or object implementing __toString() for scope of log message (e.g. callee class name or sub channel name)
     * @param array $args arbitrary number of parameters to log (need to be of known type or implement __toString())
     *
     * @return void
     *
     * @throws \InvalidArgumentException when there's no logger configured for the given logger name
     */
    public function logTo($logger_name, $log_level, $scope, $args)
    {
        if (!\AgaviConfig::get('core.use_logging', true))
        {
            return;
        }

        /* @var $logger Honeybee\Agavi\Logging\Logger */
        $logger = $this->getLogger($logger_name);

        if (!$logger)
        {
            throw new \InvalidArgumentException("Can't find logger with name '$logger_name'. Please specify another name or define the logger in the logging.xml file.");
        }

        $logger_message = $this->createLoggerMessage($log_level, $scope, $args);

        $logger->log($logger_message);
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

    /**
     * @return bool true if argument is an associative array. False otherwise.
     */
    public static function isAssoc($a)
    {
        if (!is_array($a) || empty($a))
        {
            return false;
        }

        foreach (array_keys($a) as $k => $v)
        {
            if ($k !== $v)
            {
                return true;
            }
        }

        return false;
    }
}
