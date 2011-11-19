<?php

/**
 * The ContentItem is a simple DTO style implementation of the IContentItem interface.
 * It is responseable for providing content item related data.
 *
 * @version $Id:$
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Workflow
 * @subpackage Item
 */
class ContentItem implements IContentItem
{
    /**
     * Holds our parent's (IWorkflowItem) identifier.
     *
     * @var string
     */
    protected $parentIdentifier;

    /**
     * Holds information on who created this item and when.
     *
     * @var array
     */
    protected $created;

    /**
     * Holds information on who was the last to modify this item and when.
     *
     * @var array
     */
    protected $lastModified;

    /**
     * Holds the item type (dpa, rss ...)
     * Set by the system. Not editable.
     *
     * @var string
     */
    protected $type;

    /**
     * Holds the item's title.
     *
     * @var string
     */
    protected $title;

    /**
     * Holds the item priority.
     *
     * @var int
     */
    protected $priority;


    /**
     * Holds the item's category.
     * The available categories are configured inside the settings.xml.
     *
     * @var string
     */
    protected $category;

    /**
     * Holds the item's teaser text.
     *
     * @var string
     */
    protected $teaser;

    /**
     * Holds the item's main content text.
     *
     * @var string
     */
    protected $text;

    /**
     * Holds the item's source (editor set).
     *
     * @var string
     */
    protected $source;

    /**
     * Holds a valid url using the http or https scheme
     * and that shall be linked with the content.
     *
     * @var string
     */
    protected $url;

    /**
     * Holds the item's date information (from, untill).
     *
     * @var array
     */
    protected $date;

    /**
     * Holds the item's location.
     *
     * @var IItemLocation
     */
    protected $location;

    /**
     * Creates a new ContentItem instance.
     */
    public function __construct(array $data = array())
    {
        $this->hydrate($data);
    }
    
    /**
     * Returns the unique identifier of our aggregate root (IWorkflowItem).
     *
     * @return string
     *
     * @see IWorkflowItem::getIdentifier()
     */
    public function getParentIdentifier()
    {
        return $this->parentIdentifier;
    }

    /**
     * Returns the IContentItem's created date as an array,
     * containing data about by whom and when the item was created.
     * The provided date data is a ISO8601 UTC formatted string.
     * The provided user information is a string holding the username.
     *
     * @return array
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Returns the IContentItem's created date as an array,
     * containing data about by whom and when the item modified the last time.
     * The provided date data is a ISO8601 UTC formatted string.
     * The provided user information is a string holding the username.
     *
     * @return array
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }

    /**
     * Returns the IContentItem's type.
     * Relates to the content's origin like: dpa-regio or rss.
     * This is info is not editable (system write only).
     * The source member serves the same purpose and allows editing.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns the IContentItem's priority.
     * The priority defines how important this item is compared to others.
     * May be an int from 1-3.
     *
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Returns the IContentItem's category.
     * Will be something relating to the kind of content which is contained.
     * Probally somthing like: 'Kiezleben', 'Polizeimeldung' or 'Kultur' ...
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Holds the content's title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Holds the content's teaser text.
     *
     * @return string
     */
    public function getTeaser()
    {
        return $this->teaser;
    }

    /**
     * Returns the main content text.
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Returns content-items source.
     * Holds data akin to the type attribute,
     * with the difference that the type attribute is set by the system
     * whereas the source may be set by an editor.
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Returns a valid url using the http or https scheme
     * and that shall be linked with the content.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Returns an array that represents a date interval,
     * holding an ISO8601 UTC formatted date string for two keys "from" and "untill".
     *
     * @return array
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Returns the ContentItem's location data.
     *
     * @return IItemLocation
     */
    public function getLocation()
    {
        return $this->location;
    }
    
    /**
     * Sets the item's location.
     * When an array is passed a new location instance is created and passed
     * in the given data.
     * 
     * @param mixed $location Either an array or an IITemLocation instance.
     */
    public function setLocation($location)
    {
        if (is_array($location))
        {
            $this->location = new ItemLocation($location);
        }
        elseif ($location instanceof IItemLocation)
        {
            $this->location = $location;
        }
        else
        {
            throw new Exception(
                "Invalid argument type passed to setLocation method. Only array and IItemLocation are supported."
            );
        }
    }

    /**
     * Returns an array representation of the IContentItem.
     *
     * @return string
     */
    public function toArray()
    {
        $props = array(
            'parentIdentifier', 'created', 'lastModified',
            'type', 'title', 'priority', 'category', 'teaser', 'text',
            'url', 'date', 'location', 'source'
        );
        $data = array();
        foreach ($props as $prop)
        {
            $getter = 'get' . ucfirst($prop);
            $val = $this->$getter();
            if (is_object($val) && is_callable(array($val, 'toArray')))
            {
                $data[$prop] = $val->toArray();
            }
            elseif (is_scalar($val))
            {
                $data[$prop] = $val;
            }
            else
            {
                throw new InvalidArgumentException(
                    "Can only process scalar values when exporting object to array."
                );
            }
        }
        return $data;
    }
    
    /**
     * Convenience method for setting multiple values at once.
     * 
     * @param array $values 
     * 
     * @see IContentItem::applyValues()
     */
    public function applyValues(array $values)
    {
        $writeableProps = array(
            'type', 'title', 'priority', 'category', 'teaser', 'text',
            'url', 'date', 'location', 'source'
        );
        foreach ($writeableProps as $prop)
        {
            if (array_key_exists($prop, $data))
            {
                $setter = 'set'.ucfirst($prop);
                if (is_callable(array($this, $setter)))
                {
                    $this->$setter($data[$prop]);
                }
            }
        }
    }
    
    /**
     * Hydrates the given data into the item.
     * This method is used to internally setup our state
     * and has privleged write access to all properties.
     * Properties that are set during hydrate dont mark the item as modified.
     * 
     * @param array $data 
     */
    protected function hydrate(array $data)
    {
        $simpleProps = array(
            'parentIdentifier', 'created', 'lastModified',
            'type', 'title', 'priority', 'category', 'teaser', 'text',
            'url', 'date', 'location', 'source'
        );
        foreach ($simpleProps as $prop)
        {
            if (array_key_exists($prop, $data))
            {
                $setter = 'set'.ucfirst($prop);
                if (is_callable(array($this, $setter)))
                {
                    $this->$setter($data[$prop]);
                }
                else
                {
                    $this->$prop = $data[$prop];
                }
            }
        }
    }
}

?>
