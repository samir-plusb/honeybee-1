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
class WorkflowItem implements IWorkflowItem, Zend_Acl_Resource_Interface
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
     * Holds the current state of the workflow item,
     * meaning, workflow step and owner.
     *
     * @var array
     */
    protected $currentState = array(
        'workflow' => NULL,
        'step'     => NULL,
        'owner'    => NULL
    );

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
     * Return the resource identifier for this model.
     *
     * @todo This is too concrete to be inside the workflow package.
     * The goal is to have WorkflowItem as an abstract base for the data models
     * of other packages such as news, shofi or events etc.
     * This would result in a NewsItem -> WorkflowItem, EventItem -> WorkflowItem ...
     * relationship, allowing these package specific data models to be processed as workflow resources,
     * just as the workflow item at the moment.
     * For this change to work we would use a data model's resource-id as the couchdb type attribute,
     * when persisting and would have to map the data back to the different WorkflowItem subtypes
     * instead of always instantiating plain WorkflowItems. As the WorkflowItem class will then be abstract,
     * this would not be possible any way.
     *
     * @return string
     */
    public function getResourceId()
    {
        return 'news';
    }

    public function getOwnerName()
    {
        $ticket = NULL;
        try
        {
            $supervisor = Workflow_SupervisorModel::getInstance();
            $ticket = $supervisor->getTicketPeer()->getTicketById($this->ticketId);
        }
        catch (CouchdbClientException $e)
        {
            return FALSE;
        }

        return $ticket->getCurrentOwner();
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
     *
     * @return IWorkflowItem This instance for fluent api support.
     */
    public function bumpRevision($revision)
    {
        $this->revision = $revision;
        return $this;
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
     *
     * @return IWorkflowItem This instance for fluent api support.
     */
    public function setTicket(WorkflowTicket $ticket)
    {
        $this->ticketId = $ticket->getIdentifier();
        return $this;
    }

    /**
     * Return the item's current state in the workflow,
     * meaning the workflow step it's in and who owns it at the moment.
     *
     * @return array
     */
    public function getCurrentState()
    {
        return $this->currentState;
    }

    /**
     * Updates the item's current workflow state (step and owner).
     *
     * @param string $state
     *
     * @return IWorkflowItem This instance for fluent api support.
     */
    public function updateCurrentState(array $state)
    {
        $this->currentState = $state;
        return $this;
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
     *
     * @return IWorkflowItem This instance for fluent api support.
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
        return $this;
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
     *
     * @todo Rename this methosd to set or whatever, but create* is not a good name.
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
            $this->importItem->touch();
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
        $this->importItem->touch();
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
     * Add the given content-item to the WorkflowItem's content-item list.
     *
     * @param IContentItem|array $contentItem
     *
     * @return bool True when the item was added and false if the item allready has been added.
     */
    public function addContentItem($contentItem)
    {
        $item = $contentItem;
        if (is_array($item))
        {
            if (! isset($item['identifier']))
            {
                throw new InvalidArgumentException(
                    "Missing identifier for addContentItem call." . PHP_EOL .
                    "Make sure to pass an identifier either inside the passed data array or as method param."
                );
            }
            $item = new ContentItem($contentItem);
        }
        else if(! ($contentItem instanceof IContentItem))
        {
            throw new InvalidArgumentException(
                "Invalid argument type given for the contentItem argument." . PHP_EOL .
                "Make sure to pass either an array or IContentItem instance."
            );
        }
        if (isset($this->contentItems[$item->getIdentifier()]))
        {
            return FALSE;
        }
        $this->contentItems[$item->getIdentifier()] = $item;
        return TRUE;
    }

    public function updateContentItem(array $data, $identifier = NULL)
    {
        $contentItemId = $identifier;
        if (! $contentItemId)
        {
            if (! isset($data['identifier']))
            {
                throw new InvalidArgumentException(
                    "Missing identifier for updateContentItem call." . PHP_EOL .
                    "Make sure to pass an identifier either inside the passed data array or as method param."
                );
            }
            $contentItemId = $data['identifier'];
        }
        if (! isset($this->contentItems[$contentItemId]))
        {
            return FALSE;
        }
        $this->contentItems[$contentItemId]->applyValues($data);
        return TRUE;
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
            'importItem', 'attributes', 'ticketId', 'currentState'
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
        $simpleProps = array(
            'identifier', 'revision', 'created',
            'lastModified', 'attributes', 'ticketId', 'currentState');
        $couchMappings = array('identifier' => '_id', 'revision' => '_rev');
        foreach ($simpleProps as $prop)
        {
            if (isset($couchMappings[$prop])
                && array_key_exists($couchMappings[$prop], $data)
                || array_key_exists($prop, $data))
            {
                $value = (isset($couchMappings[$prop])
                    && array_key_exists($couchMappings[$prop], $data))
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
                $this->addContentItem(new ContentItem($contentItemData));
            }
        }
        if (isset($data['importItem']))
        {
            $this->importItem = new ImportItem($data['importItem']);
        }
    }

    public function delete($remove = FALSE)
    {
        if ($remove)
        {
            return Workflow_SupervisorModel::getInstance()->getItemPeer()->deleteItem($this);
        }
        $this->attributes['marked_deleted'] = TRUE;
        return Workflow_SupervisorModel::getInstance()->getItemPeer()->storeItem($this);

    }
}

?>
