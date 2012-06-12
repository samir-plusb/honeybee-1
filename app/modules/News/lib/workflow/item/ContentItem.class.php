<?php

/**
 * The ContentItem is a simple DTO style implementation of the IContentItem interface.
 * It is responseable for providing content item related data.
 *
 * @version $Id$
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package News
 * @subpackage Workflow/Item
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ContentItem extends BaseDocument implements IContentItem
{
    /**
     * Holds our parent's (IWorkflowItem) identifier.
     *
     * @var string
     */
    protected $parentIdentifier;

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
     * Holds the item's tags.
     *
     * @var array
     */
    protected $tags = array();

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
     * Holds the item's (last)publisher.
     *
     * @var string
     */
    protected $publisher;

    /**
     * Holds the item's (last)published date.
     *
     * @var string
     */
    protected $publishDate;

    /**
     * Holds the item's location.
     *
     * @var IItemLocation
     */
    protected $location;

    /**
     * Create a fresh ContentItem instance from the given the data and return it.
     *
     * Example value structure for the $data argument,
     * which is the same structure as the toArray method's return.
     * 
     * <pre>
     * array(
     *  'parentIdentifier'    => 'foobar',
     *  'created'             => array(
     *      'date' => '05-23-1985T15:23:78.123+01:00',
     *      'user' => 'shrink0r'
     *   ),
     *   'lastModified'       => array(
     *      'date' => '06-25-1985T15:23:78.123+01:00',
     *      'user' => 'shrink0r'
     *    ),
     *    // Content Data
     *    'type'              => 'mail',
     *    'priority'          => 2,
     *    'title'             => 'Neue Termine: 42 for is the answer',
     *    'text'              => 'Der Verein ist ein Verein',
     *    'teaser'            => 'and the teaser will get u to read the text',
     *    'category'          => 'Kiezleben',
     *    'source'            => 'Bezirksamt Pankow',
     *    'url'               => 'http://www.lookmomicanhazurls.com',
     *    'isevent'           => FALSE,
     *    'affects_wholecity' => FALSE,
     *    'relevance'         => 0,
     *    'date'              => array(
     *        'from'   => '05-23-1985T15:23:78.123+01:00',
     *        'untill' => '05-25-1985T15:23:78.123+01:00'
     *     ),
     *     'location'         => array(
     *         'coords'                   => array(
     *             'long' => '12.19281',
     *             'lat'  => '13.2716'
     *          ),
     *          'city'                    => 'Berlin',
     *          'postal_code'             => '13187',
     *          'administrative_district' => 'Pankow',
     *          'district'                => 'Prenzlauer Berg',
     *          'neighborhood'            => 'Niederschönhausen',
     *          'street'                  => 'Shrinkstreet',
     *          'house_num'               => '23',
     *          'name'                    => 'Vereinsheim Pankow - Niederschönhausen'
     *     )
     * )
     * </pre>
     *
     * @param array $data
     *
     * @return IContentItem
     */
    public static function fromArray(array $data = array())
    {
        return new self($data);
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
     * Set the item's type.
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
        $this->onPropertyChanged("type");
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
     * Set the item's priority.
     *
     * @param int $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
        $this->onPropertyChanged("priority");
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
     * Set the item's category.
     *
     * @param string $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
        $this->onPropertyChanged("category");
    }

    /**
     * Returns the IContentItem's tags.
     *
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set the item's tags.
     *
     * @param array $tags
     */
    public function setTags(array $tags)
    {
        $this->tags = $tags;
        $this->onPropertyChanged("tags");
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
     * Set the item's title.
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
        $this->onPropertyChanged("title");
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
     * Set the item's teasr.
     *
     * @param string $teaser
     */
    public function setTeaser($teaser)
    {
        $this->teaser = $teaser;
        $this->onPropertyChanged("teaser");
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
     * Set the item's text.
     *
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
        $this->onPropertyChanged("text");
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
     * Set the item's source.
     *
     * @param string $source
     */
    public function setSource($source)
    {
        $this->source = $source;
        $this->onPropertyChanged("source");
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
     * Set the item's url.
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
        $this->onPropertyChanged("url");
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
     * Set the item's 'live' period.
     *
     * @param string $date An ISO8601 formatted gmt date string.
     */
    public function setDate($date)
    {
        $this->date = $date;
        $this->onPropertyChanged("date");
    }

    /**
     * Returns the item's publisher.
     *
     * @return string
     */
    public function getPublisher()
    {
        return $this->publisher;
    }

    /**
     * Set the item's publisher.
     *
     * @param string $publisher
     */
    public function setPublisher($publisher)
    {
        $this->publisher = $publisher;
        $this->onPropertyChanged("publisher");
    }

    /**
     * Returns the item's publish date.
     *
     * @return string A ISO8601 UTC formatted date string
     */
    public function getPublishDate()
    {
        return $this->publishDate;
    }

    /**
     * Set the item's publish date.
     *
     * @param string $publishDate An ISO8601 formatted gmt date string.
     */
    public function setPublishDate($publishDate)
    {
        $this->publishDate = $publishDate;
        $this->onPropertyChanged("publishDate");
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
            $this->location = ItemLocation::fromArray($location);
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
}

?>
