<?php

/**
 * The WorkflowItem serves as the main implementation of the IWorkflowItem interface.
 * It serves as the aggregate root of all objects (DTO's) that are involved in the process of content refinement.
 *
 * @version $Id$
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Workflow
 * @subpackage Item
 */
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
     * Holds the WorkflowItem's WorkflowTicket id.
     *
     * @var string
     */
    protected $ticketId;

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
     * Holds our import-item.
     *
     * @var IImportItem
     */
    protected $importItem;

    /**
     * Returns the list of our IContentItems.
     *
     * @var array
     */
    protected $contentItems = array();

    /**
     * Holds our generic attributes collection.
     *
     * @var array
     */
    protected $attributes = array();

    /**
     * Creates a new WorkflowItem instance.
     */
    public function __construct(array $data = array())
    {
        $this->hydrate($data);
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
     * Bump the item's revision.
     *
     * @param string $revision
     */
    public function bumpRevision($revision)
    {
        $this->revision = $revision;
    }

    /**
     * Return the identifier of our ticket, if we have one.
     *
     * @return string
     */
    public function getTicketId()
    {
        return $this->ticketId;
    }

    /**
     * Set the WorklflowTicket that is responseable for this item.
     *
     * @param WorkflowTicket $ticket
     */
    public function setTicket(WorkflowTicket $ticket)
    {
        $this->ticketId = $ticket->getIdentifier();
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
     * Update the item's modified timestamp.
     * If the created timestamp has not yet been set it also assigned.
     *
     * @param AgaviUser $user An optional user to use instead of resolving the current session user.
     */
    public function touch(AgaviUser $user = NULL)
    {
        $user = $user ? $user : AgaviContext::getInstance()->getUser();
        $value = array(
            'date' => date(DATE_ISO8601),
            'user' => $user->getParameter('username', 'system')
        );
        if (! $this->created)
        {
            $this->created = $value;
        }
        $this->lastModified = $value;
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
     * Set an import-item for this workflow-item instance.
     *
     * @param mixed $importData Either an array or IImportItem instance.
     *
     * @throws Exception If the workflow-item allready has an import-item or an invalid data-type is passed.
     */
    public function createImportItem($importData)
    {
        if ($this->importItem)
        {
            throw new Exception("Import item allready exists!");
        }

        if (is_array($importData))
        {
            $importData['parentIdentifier'] = $this->getIdentifier();
            $this->importItem = new ImportItem($importData);
        }
        elseif ($importData instanceof IImportItem)
        {
            $this->importItem = $importData;
        }
        else
        {
            throw new Exception(
                "Invalid argument type passed to setImportItem method. Only array and IImportItem are supported."
            );
        }
    }

    /**
     * Update the workflow-item's import item with the given values.
     *
     * @param array $importData
     *
     * @throws Exception If we dont have an import-item.
     */
    public function updateImportItem(array $importData)
    {
        if (! $this->importItem)
        {
            throw new Exception("No import-item to update.");
        }
        $this->importItem->applyValues($importData);
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

    //@todo addContentItem
    //@todo removeContentItem

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

    /**
     * Returns an array representation of the IWorkflowItem.
     *
     * @return string
     */
    public function toArray()
    {
        $props = array(
            'identifier', 'revision', 'created', 'lastModified',
            'importItem', 'attributes', 'ticketId'
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
            elseif (! is_object($val))
            {
                $data[$prop] = $val;
            }
            else
            {
                throw new InvalidArgumentException(
                    "Can only process scalar, array and item values when exporting object to array.\n" .
                    "Errornous type encountered for property: " . $prop
                );
            }
        }
        $contentItems = array();
        foreach ($this->getContentItems() as $contentItem)
        {
            $contentItems[] = $contentItem->toArray();
        }
        $data['contentItems'] = $contentItems;
        return $data;
    }

    /**
     * Hydrates the given data into the item.
     *
     * @param array $data
     */
    protected function hydrate(array $data)
    {
        $simpleProps = array('identifier', 'revision', 'created', 'lastModified', 'attributes', 'ticketId');
        $couchMappings = array('identifier' => '_id', 'revision' => '_rev');
        foreach ($simpleProps as $prop)
        {
            if (isset($couchMappings[$prop])
                && array_key_exists($couchMappings[$prop], $data)
                || array_key_exists($prop, $data))
            {
                $value = isset($couchMappings[$prop]) && array_key_exists($couchMappings[$prop], $data)
                    ? $data[$couchMappings[$prop]]
                    : $data[$prop];
                $setter = 'set'.ucfirst($prop);
                if (is_callable(array($this, $setter)))
                {
                    $this->$setter($value);
                }
                else
                {
                    $this->$prop = $value;
                }
            }
        }
        if (isset($data['contentItems']))
        {
            $this->contentItems = array();
            foreach ($data['contentItems'] as $contentItemData)
            {
                $this->contentItems[] = new ContentItem($contentItemData);
            }
        }
        if (isset($data['importItem']))
        {
            $this->importItem = new ImportItem($data['importItem']);
        }
    }
}

?>
