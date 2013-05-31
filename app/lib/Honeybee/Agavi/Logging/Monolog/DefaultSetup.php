<?php

namespace Honeybee\Agavi\Logging\Monolog;

use Monolog\Logger;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Handler\GroupHandler;

/**
 * Returns a configured \Monolog\Logger instance that logs all messages above
 * DEBUG level only when a certain trigger level (defaults to CRITICAL) was
 * reached. The logger uses the syslog and a file to log appropriate messages.
 *
 * Please note, that by default the buffering is not restricted to a certain
 * amount of log messages. This means that for long running processes it may
 * be advisable to either configure a buffer_size or use another logger to
 * prevent the increased usage of system memory for log message buffering.
 *
 * Supported appender parameters:
 * - minimum_level: Minimum \Monolog\LogLevel to log. Defaults to DEBUG
 * - trigger_level: The \Monolog\LogLevel that triggers logging of all messages
 *                  with the specified minimum_level. Defaults to CRITICAL.
 * - buffer_size: Number of entries that should be buffered at most. Beyond
 *                that the oldest entries are removed. Defaults to 0 (no limit).
 * - channel: The channel name to use for logging. Defaults to appender name.
 * - destination: The file path to use for logging to a file (full path
 *                including file name and extension). Defaults to
 *                app/log/channel_name.log). Allowed chars: "a-zA-Z0-9_-./".
 * - syslog_identifier: The string to identify message in the syslog. Defaults
 *                      to %core.app_name% from settings.xml.
 * - syslog_facility: The Syslog RFC message facility number to use. Defaults
 *                    to LOG_USER (user-level messages).
 * - bubble: Boolean value to specify whether messages that are handled should
 *           bubble up the stack or not. Defaults to true.
 */
class DefaultSetup implements IMonologSetup
{
    /**
     * @param \AgaviLoggerAppender $appender Agavi logger appender instance to use for \Monolog\Logger instance creation
     *
     * @return \Monolog\Logger with \Monolog\Handler\FingersCrossedHandler that logs to syslog and file
     */
    public static function getMonologInstance(\AgaviLoggerAppender $appender)
    {
        // get and define all parameters and their default values
        $minimum_level = $appender->getParameter('minimum_level', Logger::DEBUG);
        $trigger_level = $appender->getParameter('trigger_level', Logger::CRITICAL);
        $buffer_size = $appender->getParameter('buffer_size', 0);
        $bubble = $appender->getParameter('bubble', true);
        $channel_name = $appender->getParameter('channel', $appender->getParameter('name', 'monolog-default'));
        $default_file_path = \AgaviConfig::get('core.app_dir') . '/log/' . $channel_name . '.log';
        $file_path = preg_replace('/[^a-zA-Z0-9-_\.\/]/', '', $appender->getParameter('destination', $default_file_path));
        $syslog_identifier = $appender->getParameter('syslog_identifier', \AgaviConfig::get('core.app_name', __METHOD__));
        $syslog_facility = $appender->getParameter('syslog_facility', LOG_USER);

        // create a new \Monolog\Logger instance
        $logger = new Logger($channel_name);

        // define syslog and file handlers and group them together
        $stream_handler = new StreamHandler($file_path, $minimum_level);
        $syslog_handler = new SyslogHandler($syslog_identifier, $syslog_facility, $minimum_level);
        $group_handler = new GroupHandler(array($syslog_handler, $stream_handler), $bubble);

        // define fingers crossed handler to use the group handler
        $fingers_crossed_handler = new FingersCrossedHandler($group_handler, $trigger_level, $buffer_size, $bubble);
        $logger->pushHandler($fingers_crossed_handler);

        // return the \Monolog\Logger instance to the caller
        return $logger;
    }
}
