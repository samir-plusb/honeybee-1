<?php

/**
 * ProjectEvent is a simple DTO that holds event related information
 * and is used by the ProjectEventProxy class to publish events.
 *
 * @version $Id: ProjectEvent.class.php -1   $
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Project
 * @subpackage Event
 */
class ProjectEvent implements IEvent
{
    // ---------------------------------- <MEMBERS> ----------------------------------------------

    /**
     * Holds the object that is emitting the event.
     *
     * @var mixed
     */
    protected $sendeR;

    /**
     * Holds our generic event data.
     *
     * @var array
     */
    private $data;

    /**
     * Holds our event name.
     *
     * @var string
     */
    private $name;

    // ---------------------------------- </MEMBERS> ---------------------------------------------


    // ---------------------------------- <CONSTRUCTOR> ------------------------------------------

    /**
     * Create a new ProjectEvent instance.
     *
     * @param string $name
     * @param array $data
     */
    public function __construct($sender, $name, array $data = array())
    {
        $this->sender = $sender;
        $this->name = $name;
        $this->data = $data;
    }

    // ---------------------------------- </CONSTRUCTOR> -----------------------------------------


    // ---------------------------------- <IEvent IMPL> ------------------------------------------

    /**
     * Return the event's sender.
     *
     * @return mixed Will be an object in most cases.
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * Return the events name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return the event's data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    // ---------------------------------- </IEvent IMPL> -----------------------------------------
}

?>