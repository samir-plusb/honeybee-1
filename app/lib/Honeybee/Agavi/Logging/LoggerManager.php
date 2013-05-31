<?php

namespace Honeybee\Agavi\Logging;

use Honeybee\Core\Dat0r\Module;
use Honeybee\Core\Dat0r\Document;

/**
 * Extends \AgaviLoggerManager with log level specific convenience methods.
 */
class LoggerManager extends \AgaviLoggerManager implements ILogger//, \Psr\Log\LoggerAwareInterface
{
    /**
     * @var string to use as default scope in log message
     */
    const DEFAULT_MESSAGE_SCOPE = 'default';

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
     * - \Exception (see method self::getExceptionAsStringWithAgaviInformation())
     * - Honeybee\Core\Dat0r\Module - name of module
     * - Honeybee\Core\Dat0r\Document - uuid of document
     * - \AgaviValidationManager - messages of all incidents
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

        $scope = trim($scope);
        if (!empty($scope))
        {
            $text_message_parts = array('[' . $scope . ']');
        }

        foreach ($log_message_parts as $log_message_part)
        {
            if ($log_message_part instanceof \Exception)
            {
                $log_message_part = $this->getExceptionAsStringWithAgaviInformation($log_message_part);
            }
            elseif (is_object($log_message_part))
            {
                if ($log_message_part instanceof Module)
                {
                    $log_message_part = 'Module (Name=' . $log_message_part->getName() . ')';
                }
                elseif ($log_message_part instanceof Document)
                {
                    $log_message_part = 'Document (Identifier=' . $log_message_part->getIdentifier() . ')';
                }
                elseif ($log_message_part instanceof \AgaviValidationManager)
                {
                    $validation_messages = array();
                    foreach ($log_message_part->getErrorMessages() as $incident)
                    {
                        if (!empty($incident['message']))
                        {
                            $validation_messages[] = $incident['message'];
                        }
                    }

                    $log_message_part = 'Validation Errors (' . implode(', ', $validation_messages) . ')';
                }
                else
                {
                    if (!is_callable(array($log_message_part, '__toString')))
                    {
                        throw new \InvalidArgumentException("Can't log object '" . get_class($log_message_part) . "' as it's neither a known class nor does it have a '__toString()' method.");
                    }
                }
            }
            elseif (is_array($log_message_part))
            {
                $log_message_part = print_r($log_message_part, true);
            }

            $text_message_parts[] = (string) $log_message_part;
        }

        $class_name = $this->getDefaultMessageClass();

        $logger_message = new $class_name();
        $logger_message->setLevel($log_level);
        $logger_message->setMessage(implode(' ', $text_message_parts));

        return $logger_message;
    }

    /**
     * Returns a string with exception message enhanced by various information
     * like Agavi and PHP version, timestamp, request and routing information.
     *
     * @param \Exception $exception exception to create a log message string for
     *
     * @return string with exception message and further information
     */
    public function getExceptionAsStringWithAgaviInformation(\Exception $exception)
    {
        $misc_data = array();

        $misc_data['Agavi Version'] = \AgaviConfig::get('agavi.version');
        $misc_data['PHP Version'] = phpversion();
        $misc_data['System'] = php_uname();
        $misc_data['Timestamp'] = gmdate(DATE_ISO8601);

        if (null !== ($request = $this->getContext()->getRequest()))
        {
            if ($request instanceof \AgaviWebRequest)
            {
                $misc_data['Request URL'] = $request->getUrl();
                $misc_data['Request Method'] = $request->getMethod();
            }
            elseif ($request instanceof \AgaviConsoleRequest)
            {
                $misc_data['Input'] = $request->getInput();
            }

            $matched_routes = $request->getAttribute('matched_routes', 'org.agavi.routing');

            if ($matched_routes)
            {
                $misc_data['Matched Routes (' . count($matched_routes) . ')'] = implode(', ', $matched_routes);
            }

            if (!$request->isLocked())
            {
                $misc_data['Parameter Names (Request)'] = implode(', ', array_keys($request->getRequestData()->getParameters()));
            }
        }

        foreach ($misc_data as $key => $value)
        {
            $message_parts[] = str_pad(' ', 30 - strlen($key)) . $key . ': ' . $value;
        }

        return (string) $exception . PHP_EOL . implode(PHP_EOL, $message_parts);
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
}
