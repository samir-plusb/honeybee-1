<?php

namespace Honeybee\Agavi;

use Honeybee\Agavi\Logging\Logger;

/**
 * This context registers a shutdown listener that gives all project classes
 * the ability to gracefully handle e.g. fatal errors by registering themselves
 * for a shutdown notification. They just have to implement IShutdownListener
 * and will be notified upon shutdown with the last error from PHP.
 */
class Context extends \AgaviContext
{
    /**
     * Amount of reserved memory in bytes that is released when an error occurs.
     * This is just a funny thing to do. Not sure if it is worth the IO on every
     * request. TODO: reevaluate this sometime
     */
    const EMERGENCY_RESERVED_MEMORY = 81920;

    /**
     * @var array associative array with keys 'listener' and 'scope'
     */
    protected $shutdown_listeners = array();

    /**
     * @var string contains as many 'x' characters as the amount of preserved memory
     */
    protected $reserved_memory;

    /**
     * @var array map of PHP errors to Honeybee/Agavi log levels
     */
    public static $error_to_log_level_map = array(
        E_ERROR             => Logger::CRITICAL,
        E_PARSE             => Logger::CRITICAL,
        E_CORE_ERROR        => Logger::CRITICAL,
        E_COMPILE_ERROR     => Logger::CRITICAL,
        E_USER_ERROR        => Logger::CRITICAL,
        E_RECOVERABLE_ERROR => Logger::CRITICAL,

        E_WARNING           => Logger::WARNING,
        E_NOTICE            => Logger::WARNING,
        E_CORE_WARNING      => Logger::WARNING,
        E_COMPILE_WARNING   => Logger::WARNING,
        E_USER_WARNING      => Logger::WARNING,
        E_USER_NOTICE       => Logger::WARNING,
        E_DEPRECATED        => Logger::WARNING,
        E_USER_DEPRECATED   => Logger::WARNING,

        E_STRICT            => Logger::NOTICE,
    );

    /**
     * @var array php errors critical enough to trigger notification of registered listeners
     */
    public static $critical_errors = array(
        E_ERROR,
        E_PARSE,
        E_CORE_ERROR,
        E_COMPILE_ERROR,
        E_USER_ERROR,
        E_RECOVERABLE_ERROR,
    );

    /**
     * Register a shutdown function that may be used by all of our classes.
     */
    public function initialize()
    {
        register_shutdown_function(array($this, 'handleShutdown'));

        $this->reserved_memory = str_repeat('x', self::EMERGENCY_RESERVED_MEMORY);

        parent::initialize();
    }

    /**
     * Adds given listener to current chain of shutdown listeners registered.
     *
     * @param IShutdownListener $listener instance of class that wants to be called upon fatal errors
     */
    public function addShutdownListener(IShutdownListener $listener, $scope = IShutdownListener::NOTIFY_SCOPE_GLOBAL)
    {
        $this->shutdown_listeners[] = array('listener' => $listener, 'scope' => $scope);
    }

    /**
     * Removes given listener from current chain of shutdown listeners
     * independent from existing scopes.
     *
     * @param IShutdownListener $listener instance of class that wants to be called upon fatal errors
     */
    public function removeShutDownListener(IShutdownListener $listener_to_remove)
    {
        $listeners = array();

        foreach ($this->shutdown_listeners as $current_listener)
        {
            if ($listener_to_remove !== $current_listener['listener'])
            {
                $listeners[] = $current_listener;
            }
        }

        $this->shutdown_listeners = $listeners;
    }

    /**
     * Notifies all registered shutdown listeners.
     */
    public function handleShutdown()
    {
        $error = error_get_last();

        if (empty($error))
        {
            return; // nothing to do as no error occurred
        }

        // release memory as soon as CPU cycles are available or the script runs
        // out of memory - whatever occurs first - and pray that it's enough :-)
        unset($this->reserved_memory);

        // try to log php error according to it's severity
        $log_level = isset(self::$error_to_log_level_map[$error['type']]) ? self::$error_to_log_level_map[$error['type']] : Logger::ERROR;
        $this->getLoggerManager()->logTo('error', $log_level, 'SHUTDOWN', print_r($error, true));

        // just return if there's no critical error
        if (!in_array($error['type'], self::$critical_errors))
        {
            return;
        }

        // notify all registered shutdown listeners about critical error
        $abort_propagation = false;
        foreach ($this->shutdown_listeners as $listener)
        {
            if ($abort_propagation)
            {
                break;
            }

            if ($listener['scope'] === IShutdownListener::NOTIFY_SCOPE_GLOBAL)
            {
                $this->getLoggerManager()->logTo(null, Logger::DEBUG, 'SHUTDOWN', 'Notifying global listener "' . get_class($listener['listener']) . '" about shutdown because of critical error of type "' . $error['type'] . '".');
                $abort_propagation = $listener['listener']->onShutdown($error);
            }
            else // instance scope listeners
            {
                $file = $error['file'];
                $matches = array();
                $contents = file_get_contents($file);
                $contents = preg_replace('(/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/)', "\n", $contents);
                preg_match('~class\s+([\w\d_\\\]+)\s+~is', $contents, $matches);
                $fatal_classname = $matches[1];

                if (is_subclass_of($fatal_classname, $listener['listener']) || is_a($fatal_classname, $listener['listener']))
                {
                    $this->getLoggerManager()->logTo('default', Logger::DEBUG, 'SHUTDOWN', 'Notifying instance listener "' . get_class($listener['listener']) . '" about shutdown because of critical error of type "' . $error['type'] . '".');
                    $abort_propagation = $listener['listener']->onShutdown($error);
                }
            }
        }
    }
}
