<?php

/**
 * The EventsSchedule class reflects the structure of an event
 * taking place at different locations on different times.
 *
 * @version $Id: EventsSchedule.class.php -1   $
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Events
 * @subpackage Workflow/Item
 */
class EventsSchedule extends BaseDataObject
{
    /**
     * Holds a list of EventsLocation instances,
     * representing the different locations an event is taking place.
     *
     * @var array $locations
     */
    protected $locations;

    /**
     * Factory method for creating new EventsSchedule instances.
     *
     * @var array $data
     *
     * @return EventsSchedule
     */
    public static function fromArray(array $data = array())
    {
        return new self($data);
    }

    /**
     * Returns the locations this event is taking place.
     *
     * @var array A list of EventsLocation.
     */
    public function getLocations()
    {
        return $this->locations;
    }

    /**
     * Sets the locations the event is supposed to take place.
     *
     * @var array $locations A list of EventsLocation or EventsLocation array representations.
     */
    protected function setLocations(array $locations)
    {
        $this->locations = array();
        foreach ($locations as $locationData)
        {
            if ($locationData instanceof EventsLocation)
            {
                $this->locations[] = $locationData;
            }
            elseif (is_array($locationData))
            {
                $this->locations[] = EventsLocation::fromArray($locationData);
            }
        }
    }
}
