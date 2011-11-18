<?php

/**
 * The ImportItem is a simple DTO style implementation of the IImportItem interface.
 * It is responseable for providing import item related data.
 *
 * @version $Id:$
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Workflow
 * @subpackage Item
 */
class ImportItem implements IImportItem
{
    /**
     * Holds the ImportItem's parentIdentifier.
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
     * Holds the ImportItem's source.
     *
     * @var string
     */
    protected $source;

    /**
     * Holds the ImportItem's origin.
     *
     * @var string
     */
    protected $origin;

    /**
     * Holds the ImportItem's content timestamp.
     * Should be a ISO8601 UTC date string.
     *
     * @var string
     */
    protected $timestamp;

    /**
     * Holds the ImportItem's title.
     *
     * @var string
     */
    protected $title;

    /**
     * Holds the ImportItem's textual content.
     *
     * @var string
     */
    protected $content;

    /**
     * Holds the ImportItem's category.
     *
     * @var string
     */
    protected $category;

    /**
     * Holds the ImportItem's media.
     *
     * @var array
     */
    protected $media;

    /**
     * Holds the ImportItem's geo data.
     *
     * @var array
     */
    protected $geoData;

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
     * Returns the ImportItem's source,
     * hence a string representing the content provider that delivered the data.
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

     /**
     * Returns an uri pointing to the resource that we originate from.
     * Always is a uri, but may hold a custom scheme.
     *
     * @return string
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * Returns an ISO8601 UTC date string that holds a timestamp,
     * that is associated with the ImportItem's content.
     *
     * @return string
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Returns the ImportItem's content title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Returns the ImportItem's main content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Returns the ImportItem's category.
     * The structure of the data carried inside the string may vary depending on our source.
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Returns a list of id's that can be used together with ProjectAssetService
     * to resolve assets.
     *
     * @return array
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * Returns array holding the geo data associated with the ImportItem.
     *
     * @return array
     */
    public function getGeoData()
    {
        return $this->geoData;
    }

    /**
     * Returns an array representation of the IImportItem.
     *
     * @return string
     */
    public function toArray()
    {
        $props = array(
            'parentIdentifier', 'created', 'lastModified',
            'source', 'origin', 'timestamp', 'title', 'content', 'category',
            'media', 'geoData'
        );
        $data = array();
        foreach ($props as $prop)
        {
            $getter = 'get' . ucfirst($prop);
            $data[$prop] = $this->$getter();
        }
        return $data;
    }
}

?>
