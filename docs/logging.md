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
conveniences available in actions and views. This includes predefined methods
for all available log levels:

- `logTrace()`
- `logDebug()`
- `logInfo()`
- `logNotice()`
- `logWarning()`
- `logError()`
- `logAlert()`
- `logCritical()`
- `logEmergency()`

and a `getDefaultLogger()` method in the base classes. The `getDefaultLogger()`
method returns the name of the logger to use for the builtin `log<Level>()`
method calls. Those methods use that logger with a default scope of the current
class name and the log level from their name.

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

You can always use the default Agavi way of logging by getting the logger
manager from the context and log a logger message via specified or default
logger instances:

```

    $this->getContext()->getLoggerManager()->log('This debug message goes to the default logger and its appenders', \AgaviLogger::DEBUG);
    $this->getContext()->getLoggerManager()->getLogger('special')->log('This error message goes to the special logger and its appenders', \AgaviLogger::ERROR);

```

## Further topics:

- convenience methods on logger instances (`log<Level>(...)`)
- `Monolog` logger instance creation and usage via setup classes
- `Monolog` usage for `FirePHP`, `ChromePHP` and `PhpDebugToolbar` appender
- usage of other log libraries like `Analog`
- examples for `logging.xml` customization
- `getDefaultLogger` vs. `scope` parameter
- PSR-3 `LoggerInterface` vs. variable method arguments
- `\AgaviLogger` bit fields explanation and usage
- suggested practices (`logrotate`, log files on other file systems etc.)

## PSR-3 compatible logging

As the PSR-3 standard seems to get some traction there is support for this as
well. As the log levels Honeybee uses via Agavi in addition to those features
differ a bit from PSR-3 and `Monolog` log levels there are some ways to use
those.

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
stacktraces logged, `\DateTime` instances get an ISO-8601 representation etc. pp.

