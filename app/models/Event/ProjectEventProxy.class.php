<?php

/**
 * ProjectEventProxy is a concrete implementation of the IEventProxy interface
 * and provides typical event routing functionality, based on event names and callbacks.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         ApplicationBase
 * @subpackage      Event
 */
class ProjectEventProxy implements IEventProxy
{
    // ---------------------------------- <MEMBERS> ----------------------------------------------

    /**
     * An array containing our current event subscriptions.
     *
     * @var         array
     */
    private $subscriptions = array();

    /**
     * Holds a static instance of this class.
     *
     * @var         ProjectEventProxy
     */
    private static $instance;

    // ---------------------------------- </MEMBERS> ---------------------------------------------


    // ---------------------------------- <IEventProxy IMPL> -------------------------------------

    /**
     * Subscribe to an event by providing the desired event name
     * and a callback that shall be invoked when the event is published.
     *
     * @param       string $eventName An event name such as 'midas.events.import.record_success'
     * @param       array $callback An array containing an object and callable public method name.
     *
     * @see         IEventProxy::subscribe()
     */
    public function subscribe($eventName, array $callback)
    {
        $this->checkCallback($callback);

        if (!isset($this->subscriptions[$eventName]))
        {
            $this->subscriptions[$eventName] = array();
        }

        $this->subscriptions[$eventName][] = $callback;
    }

    /**
     * Unsubscribe from an event by providing the desired event name
     * and a callback that shall be invoked when the event is published.
     *
     * @param       string $eventName An event name such as 'midas.events.import.record_success'
     * @param       array $callback An array containing an object and callable public method name.
     *
     * @see         IEventProxy::unsubscribe()
     */
    public function unsubscribe($eventName, array $callback)
    {
        $this->checkCallback($callback);

        if (!isset($this->subscriptions[$eventName]))
        {
            return;
        }

        list($object, $methodName) = $callback;

        $callbackIndex = -1;
        $curIndex = 0;

        foreach ($this->subscriptions[$eventName] as $curCallback)
        {
            list($curObject, $curMethodName) = $curCallback;

            if ($curObject === $object && $curMethodName === $methodName)
            {
                $callbackIndex = $curIndex;
                break;
            }

            $curIndex++;
        }

        array_splice($this->subscriptions[$eventName], $callbackIndex, 1);
    }

    /**
     * Publish the given event to all registered subscribers.
     *
     * @param       IEvent $event
     *
     * @see         IEventProxy::publish()
     */
    public function publish(IEvent $event)
    {
        if (isset($this->subscriptions[$event->getName()]) && is_array($this->subscriptions[$event->getName()]))
        {
            foreach ($this->subscriptions[$event->getName()] as $curCallback)
            {
                list($curObject, $curMethodName) = $curCallback;
                $curObject->$curMethodName($event);
            }
        }
    }

    // ---------------------------------- </IEventProxy IMPL> ------------------------------------


    // ---------------------------------- <PUBLIC METHODS> ---------------------------------------

    /**
     * Returns a static instance of this class,
     * as a workaround to easily making it available for everyone around,
     * without needing to do any stunts.
     * Yes we know global state is bad, but this is the so called occasion.
     * If you find any other global state around please file a bug.
     *
     * @return      ProjectEventProxy
     */
    public static function getInstance()
    {
        if (NULL === self::$instance)
        {
            self::$instance = new ProjectEventProxy();
        }

        return self::$instance;
    }

    // ---------------------------------- </PUBLIC METHODS> --------------------------------------


    // ---------------------------------- <WORKING METHODS> --------------------------------------

    /**
     * Checks if the given callback is callable.
     *
     * @param       array $callback
     *
     * @throws      Exception If the provided $callback is not callable.
     */
    protected function checkCallback(array $callback)
    {
        if (!is_callable($callback))
        {
            throw new Exception("Non executeable callback provided.");
        }
    }

    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>