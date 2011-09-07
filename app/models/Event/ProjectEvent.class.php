<?php

/**
 * ProjectEvent is a simple DTO that holds event related information and is used by the ProjectEventProxy class
 * to publish events.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @package         Core
 * @subpackage      Event
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 */
class ProjectEvent implements IEvent
{
    // ---------------------------------- <MEMBERS> ----------------------------------------------
    
    /**
     * Holds our generic event data.
     * 
     * @var         array $data;
     */
    private $data;
    
    /**
     * Holds our event name.
     * 
     * @var         string $name
     */
    private $name;
    
    // ---------------------------------- </MEMBERS> ---------------------------------------------
    
    
    // ---------------------------------- <CONSTRUCTOR> ------------------------------------------
    
    /**
     * Create a new ProjectEvent instance.
     * 
     * @param       string $name
     * @param       array $data
     */
    public function __construct($name, array $data = array())
    {
        $this->name = $name;
        $this->data = $data;
    }
    
    // ---------------------------------- </CONSTRUCTOR> -----------------------------------------
    
    
    // ---------------------------------- <IEvent IMPL> ------------------------------------------
    
    /**
     * Return the events name.
     * 
     * @return      string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Return the event's data.
     * 
     * @return      array
     */
    public function getData()
    {
        return $this->data;
    }
    
    // ---------------------------------- </IEvent IMPL> -----------------------------------------
}

?>