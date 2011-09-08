<?php

/**
 * The IEvent interface defines the public api for a simple event dto,
 * that is used by the IEventProxy to transport and provide event related information.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         ApplicationsBase
 * @subpackage      Event
 */
interface IEvent
{
    /**
     * Return our event name.
     * 
     * @return      string
     */
    public function getName();
    
    /**
     *  Return our event data.
     * 
     * @return      array
     */
    public function getData();
}

?>