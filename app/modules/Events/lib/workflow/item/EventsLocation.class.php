<?php

/**
 * The EventsLocation class reflects the structure of an event's
 * location and appointments at which the event is taking place.
 *
 * @version $Id: EventsSchedule.class.php -1   $
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package Events
 * @subpackage Workflow/Item
 */
class EventsLocation extends BaseDataObject
{
    /**
     * Holds a location idendtifier,
     * referring to a location from the Shofi module.
     *
     * @var string $locationId
     */
    protected $locationId;

    /**
     * Holds list of people that are involved in an event.
     *
     * @var array $involvedPeople
     */
    protected $involvedPeople;

    /**
     * Holds a list of EventsAppointment,
     * that represent the various times at which the event is in action.
     *
     * @var array $appointments A list of EventsAppointment
     */
    protected $appointments;

    /**
     * Factory method for creating new EventsLocation instances.
     *
     * @var array $data
     *
     * @return EventsLocation
     */
    public static function fromArray(array $data = array())
    {
        return new self($data);
    }

    /**
     * Returns the id of a related Shofi location identifier.
     *
     * @return string
     */
    public function getLocationId()
    {
        return $this->locationId;
    }

    /**
     * Returns an array of people that are involved in the current event.
     *
     * @return array
     */
    public function getInvolvedPeople()
    {
        return $this->involvedPeople;
    }

    /**
     * Returns an array of EventsAppointment instances,
     * that define the times at which our event is active.
     *
     * @return array
     */
    public function getAppointments()
    {
        return $this->appointments;
    }

    /**
     * Sets our related location identifier.
     *
     * @var string $locationId
     */
    protected function setLocationId($locationId)
    {
        $this->locationId = $locationId;
    }

    /**
     * Sets the people that are considered as involved in the event.
     *
     * @var array $involvedPeople
     */
    protected function setInvolvedPeople(array $involvedPeople)
    {
        $this->involvedPeople = $involvedPeople;
    }

    /**
     * Sets the appointments for our event.
     *
     * @var array $appointments A list of EventsAppointment or EventsAppointment array representations.
     */
    protected function setAppointments(array $appointments)
    {
        $this->appointments = array();
        foreach ($appointments as $appointmentData)
        {
            if ($appointmentData instanceof EventsAppointment)
            {
                $this->appointments[] = $appointmentData;
            }
            elseif (is_array($appointmentData))
            {
                $this->appointments[] = EventsAppointment::fromArray($appointmentData);
            }
        }
    }
}
