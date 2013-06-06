# Logging

Logging is configured via `app/config/logging.xml`. This file includes the file
`app/project/config/logging.xml` if it exists. In that file you may specify
your own loggers, logger appenders and layouts. If the provided loggers and
appenders are not sufficient you can use the `Monolog` logging library with
all handlers, processors and formatters.

The available log levels of Honeybee are TRACE, DEBUG, INFO, NOTICE, WARNING,
ERROR, ALERT, CRITICAL, EMERGENCY. To make use of these you should create an
\AgaviLoggerMessage instance and give that to the wanted Logger instance via
the LoggerManager.

As the creation of those message instances is a lot of typing there are some
conveniences available in _actions_ and _views_. This includes the following
predefined methods for all available log levels:

- `logTrace()`
- `logDebug()`
- `logInfo()`
- `logNotice()`
- `logWarning()`
- `logError()`
- `logAlert()`
- `logCritical()`
- `logEmergency()`

and a `getLoggerName()` method in the base classes. The `getLoggerName()`
method returns the name of the logger to use for the builtin `log<Level>()`
method calls. Those methods use that logger with a default scope of the current
class name and the log level from their name. That is, if you have actions that
should not log to the default log, but use their own topic logging you can just
override the `getLoggerName()` method with an existing logger name from the
`logging.xml` file to make your action use that instead of the default logger
for all logging calls via the `$this->log<Level>()` methods.

## Usage examples

The following are a few ways to log messages in actions and views:

```
    $this->logDebug('Trying to import entries into', $this->getModule(), "for the specified consumer '$consumer_name'.");

    $this->logError(
        'Import for {module} and consumer {consumer} failed. Exception: {cause}',
        array(
            'module' => $this->getModule(),
            'consumer' => $consumer_name,
            'cause' => $exception,
            'scope' => 'Import'
        )
    );

    $this->logError('Import for', $this->getModule(), 'and consumer', $consumer_name, 'failed. Exception was:', $e->getMessage());
    $this->logTrace('Details from Validation:', $this->getContainer()->getValidationManager(), $exception, PHP_EOL . "\nwoohooo\n\n");
    $this->logDebug($this->getModule(), 'is invalid');

    $this->logTrace('Everybody get down, this {beep}', array('beep' => 'is a robbery!!!11', 'scope' => 'YOLO'));

    $this->logCritical('{fail}', array('fail' => $e));
    $this->logCritical($e);
```

All of the above method calls are convenience shortcuts of the default Agavi
way of logging which usually is: get the logger manager from the context and
then log to a specific logger or log a message to all loggers.

```
    $this->getContext()->getLoggerManager()->getLogger('special')->log('This error message goes to the special logger and its appenders', \AgaviLogger::ERROR);
    $this->getContext()->getLoggerManager()->log('This debug message goes to all loggers and its appenders', \AgaviLogger::DEBUG);
```

The method signature of the \AgaviLoggerManager log method is as follows:

```
public function log($message, $loggerOrSeverity = null)
```

When you specify just a message it will be logged to all loggers as there is no
log level or severity given. When you specify a message and an Agavi log level
a message with that log level is created and logged to all loggers. If you
supply a logger instance or logger name that logger is used for logging.

## Logging to specific loggers

Honeybee adds some more convenience methods to log to specific loggers with
the addition of a log scope that is an additional string apart from the log
level to distinguish log messages. That scope is used by the default logger
message layout.

## Logging via Monolog

There's a builtin `Monolog` logger appender that can be utilized to use all
of Monolog's handlers, processors and formatters for logging. As the setup
of a Monolog logger needs some code and would not have been a good fit for
some arbitrary parameters of the logger appender in the `logging.xml` a common
`setup` parameter was introduced that takes a class name to instantiate. The
interface to adhere to is `Honeybee\Agavi\Logging\Monolog\IMonologSetup` which
defines only one mathod: `getMonologInstance(\AgaviLoggerAppender)`. Via such
a class you're free to create a `Monolog\Logger` instance with a configuration
that suits your requirements. To make the setup a bit more flexible you can
use the parameters of the appender instance given to the method. The appender
parameters are directly from the appropriate part of the `logging.xml` file.

There are some example setups in the `Honeybee/Agavi/Logging/Monolog` folder.
The `DefaultSetup` class creates a logger with a `FingersCrossedHandler` that
logs all message of all severities to an application log file and the syslog
only when a message of a certain configurable threshold level appears. This
means that the syslog and `critical.log` file are empty as long as there are no
log messages with a log level of `CRITICAL` within a request.

Other example setups are `FirePhpSetup` and `ChromePhpSetup` that allow the
developers to see log messages with their browser (or other application) that
supports `FirePHP` or `ChromePHP` capabilities. This includes Firebug as an
extension of Firefox and the ChromeLogger extension in Chrome or Chromium.

## Logging via other logging libraries

If you want to use other logging libraries you can create loggers, logger
appenders and logger layouts. If your goal is just to use another library
without redefining a lot of things in the logging.xml file, you should create
a logger appender for your library that converts the given Agavi logger message
to the appropriate format you want to use for your custom loggers.

To include e.g. an `Analog` handler for FirePHP logging you could do:

```
<?php
namespace Your\Namespace\Logging;

use Analog\Logger;
use Analog\Handler\FirePHP;

/**
 * Sends AgaviLoggerMessages to an \Analog\Logger instance for FirePHP logging.
 */
class AnalogLoggerAppender extends \AgaviLoggerAppender
{
    /**
     * @var logger \Analog\Logger instance
     */
    protected $logger = array();

    /**
     * Retrieve the Analog instance to write to.
     *
     * @return \Analog\Logger instance to use for logging
     */
    protected function getAnalogInstance()
    {
        if (!$this->logger)
        {
            $this->logger = new Logger();
            $this->logger->handler(FirePHP::init());
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

        $analog_level = $this->convertAgaviLevelToAnalogLevel($message->getLevel());
        $analog_message = (string) $this->getLayout()->format($message);

        $this->getAnalogInstance()->log($analog_message, $analog_level);
    }

    /**
     * @param int $log_level_or_severity One of \AgaviLogger::DEBUG etc.
     *
     * @return int one of \Analog\Logger log levels
     */
    public function convertAgaviLevelToMonologLevel($log_level_or_severity)
    {
        if (!is_int($log_level_or_severity))
        {
            throw new \InvalidArgumentException("The given log level '$log_level_or_severity' is not an integer. Please use AgaviLogger::DEBUG or similar.");
        }

        $log_level_or_severity = abs($log_level_or_severity);

        // ...here be conversion magic...

        return $level;
    }

    /**
     * Execute the shutdown procedure.
     */
    public function shutdown()
    {
        // nothing to do here for Analog handler shutdown?
    }
}
```

You are not tied to only use custom logger appenders. Via `logging.xml` file
it's possible to use custom logger classes and custom layouts as well. You may
as well format and create the given messages directly in your appender or even
logger when you override those used by default. Further it's possible to change
the default AgaviLoggerMessage class via `factories.xml` file modification.

## PSR-3 compatible logging

As the PSR-3 standard seems to get some traction there is support for this as
well. As the log levels Honeybee uses via Agavi in addition to those features
differ a bit from PSR-3 and e.g. `Monolog` log levels there are some ways to
use PSR-3 compatible logging with the default setup.

There is a `Honeybee\Agavi\Logging\Psr3Logger` class available that wraps an
\AgaviLogger instance. The `Honeybee\Agavi\Logging\Logger` has a convenience
method `getPsr3Logger()` that you can call to get the Agavi logger instance as
a PSR-3 compatible logger instance:

```
    $this->getContext()->getLoggerManager()->getLogger('default')->getPsr3Logger()->log(\Psr\Log\LogLevel::CRITICAL, 'Everybody get down, this {beep}', array('beep' => 'is a robbery!!!11'));
```

You get the logger you wish from the logger manager, ask it for a PSR-3
compatible instance of itself and log a message with the appropriate PSR-3 log
level and context needed.

The PSR-3 compatible logger replaces occurances of known types in the same way
the default Honeybee logger does. This means, that exceptions will get their
stacktraces logged, `\DateTime` instances get an ISO-8601 representation etc.

There is a builtin convenience for the default log methods to use the logging
conventions of the `LoggerInterface`. You can use a template string as a
message and supply the second method argument as an associative array with keys
as placeholder names for the templated string and their values as replacements.

The following will log an error to the default logger with scope `FOO` and the
message text `This will be replaced.`:

```
    $this->getContext()->getLoggerManager->logError("This will be {foo}", array('foo' => 'replaced.', 'scope' => 'FOO'));
```

## Suggestion

The following are all smart things to do while not all of them apply to all
situations or applications. They are listed here as a starting point before
you leave this file searching the web for "logging best practices":

- separate logs only where needed (sensitive information, different topics)
- prefer tools like `logrotate` over of rolling file appenders
- use monitoring and alerting for your logs (certain conditions, file sizes)
- put logs somewhere to visualize and query them (use e.g. logstash + kibana)
- use different settings in production and development environments
- ...

## Further topics:

- `Monolog` logger instance creation and usage via setup classes
- `Monolog` usage for `FirePHP`, `ChromePHP` and `PhpDebugToolbar` appender
- usage of other log libraries like `Analog`
- examples for `logging.xml` customization (contexts, environments, appenders)
- `getDefaultLogger` vs. `scope` parameter
- PSR-3 `LoggerInterface` vs. variable method arguments
- `\AgaviLogger` bit fields explanation and usage
- suggested practices (`logrotate`, log files on other file systems etc.)
- $this->logError("[UNAUTHORIZED] Authentication attempt failed " . $log_message_part . " Errors are: " . join(PHP_EOL, $authResponse->getErrors()));
- $lm = $this->getContext()->getLoggerManager();
- $lm->log(new \AgaviLoggerMessage("[UNAUTHORIZED] Authentication attempt failed " . $log_message_part . " Errors are: " . join(PHP_EOL, $authResponse->getErrors())), \AgaviLogger::DEBUG);
- $lm->logToAll(\AgaviLogger::TRACE, 'AUTH', "[UNAUTHORIZED] Authentication attempt failed " . $log_message_part . " Errors are: " . join(PHP_EOL, $authResponse->getErrors()));
- $lm->log($message, $loggerOrSeverity);
- $this->getContext()->getLoggerManager()->logError($log_message);
- $logger_manager->logTo('error', \AgaviLogger::ERROR, get_class($this), $log_message);
- $logger_manager->logTo(null, \AgaviLogger::ERROR, get_class($this), array($log_message, $exception, $honeybee_document));
- $logger_manager->logToAll(\AgaviLogger::ERROR, get_class($this), $log_message);

