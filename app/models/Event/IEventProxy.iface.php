<?php

/**
 * The IEventProxy interface exposes a publish/subscribe based event messageing api,
 * that provides x-component communication without coupleling.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         ApplicationBase
 * @subpackage      Event
 */
interface IEventProxy
{
    /**
     * Subscribe to an event by providing the desired event name
     * and a callback that shall be invoked when the event is published.
     * 
     * @param       string $eventName An event name such as 'midas.events.import.record_success'
     * @param       array $callback An array containing an object and callable public method name.
     */
    public function subscribe($eventName, array $callback);
    
    /**
     * Unsubscribe from an event by providing the desired event name
     * and a callback that shall be invoked when the event is published.
     * 
     * @param       string $eventName An event name such as 'midas.events.import.record_success'
     * @param       array $callback An array containing an object and callable public method name.
     */
    public function unsubscribe($eventName, array $callback);
    
    /**
     * Publish the given event to all registered subscribers.
     * 
     * @param       IEvent $event
     */
    public function publish(IEvent $event);
}

?>