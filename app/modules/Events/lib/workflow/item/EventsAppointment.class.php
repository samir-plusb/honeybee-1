<?php

/**
 * The EventsAppointment class reflects the structure of one of an event's appointments.
 *
 * @version $Id: EventsAppointment.class.php -1   $
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package Events
 * @subpackage Workflow/Item
 */
class EventsAppointment extends BaseDataObject
{
    /**
     * Holds a string representing the appointment's key.
     * This is not necessarily unique.
     *
     * @var string $key
     */
    protected $key;

    /**
     * Holds the datetime of the last content update.
     * 
     * @var string $contentUpdated Datetime string in the format: 'd-m-y H:i:s'
     */
    protected $contentUpdated;

    /**
     * Holds a bool telling if this is a recommended appointment or not.
     *
     * @var bool $isRecommended
     */
    protected $isRecommended;

    /**
     * Holds the datetime of the appointment's start datetime.
     * 
     * @var string $startDate Datetime string in the format: 'd-m-y H:i:s'
     */
    protected $startDate;

    /**
     * Holds the datetime of the appointment's end datetime.
     * 
     * @var string $endDate Datetime string in the format: 'd-m-y H:i:s'
     */
    protected $endDate;

    /**
     * Holds list of people that are involved in an event.
     *
     * @var array
     */
    protected $involvedPeople;

    /**
     * Some kind of text info, that has no better domain name.
     * Sometimes it's supposed to contain descriptive data suitable for teaser text etc.
     *
     * @var string $preText
     */
    protected $preText;

    /**
     * Holds a text describing the appointment.
     *
     * @var string $text
     */
    protected $text;

    /**
     * Some kind of text info, that has no better domain name.
     * Sometimes it's supposed to contain descriptive data suitable for teaser text etc.
     *
     * @var string $postText
     */
    protected $postText;

    /**
     * Holds a text describing any appointment specific detail.
     *
     * @var string $detail
     */
    protected $detail;

    /**
     * Factory method for creating new EventsAppointment instances.
     *
     * @var array $data
     *
     * @return EventsAppointment
     */
    public static function fromArray(array $data = array())
    {
        return new self($data);
    }

    /**
     * Returns the apointment's key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Sets the appointment's key.
     *
     * @var string $key
     */
    protected function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * Returns the apointment's content updated date.
     *
     * @return string Datetime string in the format: 'd-m-y H:i:s'
     */
    public function getContentUpdated()
    {
        return $this->contentUpdated;
    }

    /**
     * Sets the appointment's content updated date.
     *
     * @var string $contentUpdated
     */
    protected function setContentUpdated($contentUpdated)
    {
        $this->contentUpdated = $contentUpdated;
    }

    /**
     * Tells if the appointment is recommended or not.
     *
     * @return string
     */
    public function getIsRecommended()
    {
        return $this->isRecommended;
    }

    /**
     * Sets the appointment's isRecommended flag.
     *
     * @var bool $isRecommended
     */
    protected function setIsRecommended($isRecommended)
    {
        $this->isRecommended = (bool)$isRecommended;
    }

    /**
     * Returns the apointment's start date.
     *
     * @return string Datetime string in the format: 'd-m-y H:i:s'
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Sets the appointment's start date.
     *
     * @var string $startDate Datetime string in the format: 'd-m-y H:i:s'
     */
    protected function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * Returns the apointment's end date.
     *
     * @return string Datetime string in the format: 'd-m-y H:i:s'
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Sets the appointment's end date.
     *
     * @var string $endDate Datetime string in the format: 'd-m-y H:i:s'
     */
    protected function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * Returns the apointment's involved people.
     *
     * @return string
     */
    public function getInvolvedPeople()
    {
        return $this->involvedPeople;
    }

    /**
     * Sets the appointment's involved people list.
     *
     * @var string $involvedPeople
     */
    protected function setInvolvedPeople(array $involvedPeople)
    {
        $this->involvedPeople = $involvedPeople;
    }

    /**
     * Returns the apointment's preText.
     *
     * @return string
     */
    public function getPreText()
    {
        return $this->preText;
    }

    /**
     * Sets the appointment's preText.
     *
     * @var string $preText
     */
    protected function setPreText($preText)
    {
        $this->preText = $preText;
    }

    /**
     * Returns the apointment's text.
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Sets the appointment's text.
     *
     * @var string $text
     */
    protected function setText($text)
    {
        $this->text = $text;
    }

    /**
     * Returns the apointment's postText.
     *
     * @return string
     */
    public function getPostText()
    {
        return $this->postText;
    }

    /**
     * Sets the appointment's postText.
     *
     * @var string $postText
     */
    protected function setPostText($postText)
    {
        $this->postText = $postText;
    }

    /**
     * Returns the apointment's detail.
     *
     * @return string
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * Sets the appointment's detail.
     *
     * @var string $detail
     */
    protected function setDetail($detail)
    {
        $this->detail = $detail;
    }
}
