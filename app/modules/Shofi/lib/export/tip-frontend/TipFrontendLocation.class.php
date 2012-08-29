<?php

/**
 * The TipFrontendLocation class is represents the structure of ShofiWorkflowItems
 * from the view of the TipEvent-frontend.
 *
 * @version         $Id: TipFrontendLocation.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.Schmitt-rink@berlinonline.de>
 * @package         Events
 * @subpackage      Export/TipFrontend
 */
class TipFrontendLocation extends BaseDocument
{
    /**
     * Holds the location document's revision.
     *
     * @var string $name
     */
    protected $revision;

    /**
     * Holds the location locations core-data.
     *
     * @var array $coreData
     */
    protected $coreData;

    /**
     * Holds the location locations sales-data.
     *
     * @var array $salesData
     */
    protected $salesData;

    /**
     * Holds the location locations detail-data.
     *
     * @var array $detailData
     */
    protected $detailData;

    /**
     * Holds a key-value list of generic attributes.
     *
     * @var aray $attributes
     */
    protected $attributes;

    /**
     * Holds the location's category.
     *
     * @var string $category
     */
    protected $category;

    /**
     * Holds the location's category.
     *
     * @var string $category
     */
    protected $subcategory;

    /**
     * Holds a list of public transports that are near this location.
     *
     * @var array $publicTransports
     */
    protected $publicTransports;

    /**
     * Holds the location document's revision.
     *
     * @var string $name
     */
    protected $lastModified;

    /**
     * Holds the location's events.
     *
     * @var array $events
     */
    protected $events = array();

    /**
     * Factory method for creating new TipFrontendLocation instances.
     *
     * @var array $data
     *
     * @return TipFrontendLocation
     */
    public static function fromArray(array $data = array())
    {
        return new self($data);
    }

    /**
     * Returns the event location document's revision.
     *
     * @return string
     */
    public function getRevision()
    {
        return $this->revision;
    }

    /**
     * Sets the event location document's revision.
     *
     * @var string $revision The new revision.
     */
    public function setRevision($revision)
    {
        $this->revision = $revision;
        $this->onPropertyChanged("revision");
        return $this;
    }

    /**
     * Returns a list of TipFrontendEvent.
     *
     * @return array
     */
    public function getEvent()
    {
        return $this->events;
    }

    /**
     * Sets the frontend location's events.
     *
     * @var array List of TipFrontendEvent or equivalent array representations
     */
    protected function setEvents(array $events)
    {
        $this->events = array();
        foreach ($events as $event)
        {
            if ($event instanceof TipFrontendEvent)
            {
                $this->events[] = $event;
            }
            elseif (is_array($event) && ! empty($event))
            {
                $this->events[] = TipFrontendEvent::fromArray($event);
            }
        }
    }
}
