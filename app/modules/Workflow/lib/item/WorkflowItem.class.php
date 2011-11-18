<?php

class WorkflowItem implements IWorkflowItem
{
    /**
     * Holds the WorkflowItem's identifier.
     *
     * @var string
     */
    protected $identifier;

    /**
     * Holds the WorkflowItem's revision.
     *
     * @var string
     */
    protected $revision;

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
     *
     *
     * @var IImportItem
     */
    protected $importItem;

    /**
     * Returns the list of our IContentItems.
     *
     * @var array
     */
    protected $contentItems;

    /**
     * Holds our generic attributes collection.
     *
     * @var array
     */
    protected $attributes;

    /**
     * Creates a new WorkflowItem instance.
     */
    public function __construct(array $data = array())
    {
        // hydrate the data.
    }

    /**
     * Returns the system wide unique identifier of the IWorkflowItem.
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Returns the IWorkflowItem's current revision.
     *
     * @return string
     */
    public function getRevision()
    {
        return $this->revision;
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
     * Return our related import item.
     *
     * @return IImportItem
     */
    public function getImportItem()
    {
        return $this->importItem;
    }

    /**
     * Return a list of content items that belong to this workflow item.
     *
     * @return array An list of of IContentItems
     */
    public function getContentItems()
    {
        return $this->contentItems;
    }

    /**
     * Return a generic assoc array of attributes.
     * @todo Implement an AttributeHolder for this?
     *
     * @return array A plain key=>value collection.
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
}

?>
