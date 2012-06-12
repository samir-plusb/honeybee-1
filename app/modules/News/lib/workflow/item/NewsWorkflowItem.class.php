<?php

/**
 * The NewsWorkflowItem extends the WorkflowItem to add content-items,
 * a collection of documents that have been gained from processing the NewsMasterRecord.
 * Content-items reflect the dataset that is provided to the consumers and are created by
 * human editors (content-workers) that refine one or more content-items for each workflow-item's masterrecord.
 *
 * @version $Id$
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package News
 * @subpackage Workflow/Item
 */
class NewsWorkflowItem extends WorkflowItem
{
    /**
     * Holds a list with our IContentItems.
     *
     * @var array
     */
    protected $contentItems = array();

    public function determineWorkflow()
    {
        return 'news';
    }

    /**
     * Returns the name of our ticket's current owner.
     *
     * @return string Name of our ticket's owner or NULL if ticket doesn't exist.
     */
    public function getOwnerName()
    {
        $ticket = NULL;
        $supervisor = WorkflowSupervisorFactory::createByTypeKey('news');
        $ticket = $supervisor->getWorkflowTicketStore()->fetchByIdentifier($this->getTicketId());
        return $ticket ? $ticket->getCurrentOwner() : NULL;
    }

    /**
     * Create a fresh NewsWorkflowItem instance from the given the data and return it.
     *
     * Example value structure for the $data argument,
     * which is the same structure as the toArray method's return.
     *
     * <pre>
     * array(
     *     'identifier' => 'foobar',
     *     'revision' => '1-15394a6853828769ee1be885909548b3',
     *     'ticketId' => '12jk1hjh132jbasdl2',
     *     'created' => array(
     *         'date' => '05-23-1985T15:23:78.123+01:00',
     *         'user' => 'shrink0r'
     *     ),
     *     'lastModified' => array(
     *         'date' => '06-25-1985T15:23:78.123+01:00',
     *         'user' => 'shrink0r'
     *     ),
     *     'masterRecord' => @see MasterRecord::toArray(),
     *     'contentItems' => array(
     *         1 => @see IContentItem::toArray(),
     *         2 => ...,
     *         ...
     *     ),
     *     'attributes' => array(
     *         'someKey' => 'over the value',
     *         ...
     *     )
     * )
     * </pre>
     *
     * @param array $data
     *
     * @return IWorkflowItem
     */
    public static function fromArray(array $data = array())
    {
        return new NewsWorkflowItem($data);
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
     * Get one of our content-items by id.
     *
     * @param string $contentItemId
     *
     * @return IContentItem Or null if we dont have an item for the given id.
     */
    public function getContentItem($contentItemId)
    {
        if (isset($this->contentItems[$contentItemId]))
        {
            return $this->contentItems[$contentItemId];
        }
        return NULL;
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
        if (is_array($contentItem))
        {
            $data = $contentItem;
            if (! isset($data['identifier']))
            {
                throw new InvalidArgumentException(
                    "Missing identifier for addContentItem call." . PHP_EOL .
                    "Make sure to pass an identifier either inside the passed data array or as method param."
                );
            }
            $data['parentIdentifier'] = $this->getIdentifier();
            $contentItem = ContentItem::fromArray($data);
        }

        if(! ($contentItem instanceof IContentItem))
        {
            throw new InvalidArgumentException(
                "Invalid argument type given for the contentItem argument." . PHP_EOL .
                "Make sure to pass either an array or IContentItem instance."
            );
        }

        if (NULL === $this->getContentItem($contentItem->getIdentifier()))
        {
            $this->contentItems[$contentItem->getIdentifier()] = $contentItem;
            $this->onPropertyChanged("contentItems");
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Remove the given content-item.
     *
     * @param IContentItem $contentItem
     */
    public function removeContentItem(IContentItem $contentItem)
    {
        $contentItems = array();
        foreach ($this->contentItems as $curItem)
        {
            if ($curItem->getIdentifier() !== $contentItem->getIdentifier())
            {
                $contentItems[] = $curItem;
            }
        }
        $this->contentItems = $contentItems;
        $this->onPropertyChanged("contentItems");
    }

    /**
     * Update the content-item for the given identifier with the passed data.
     *
     * @param array $data
     * @param string $identifier
     *
     * @return boolean
     *
     * @throws InvalidArgumentException
     */
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

    /**
     * Set our content-items (usually only called during db-result hydrate).
     *
     * @param array $contentItems
     */
    protected function setContentItems(array $contentItems)
    {
        $this->contentItems = array();
        foreach ($contentItems as $contentItemData)
        {
            $this->addContentItem(
                ContentItem::fromArray($contentItemData)
            );
        }
    }

    /**
     * Return the name of the class to use as the IMasterRecord implementation for this class.
     *
     * @return string
     */
    protected function getMasterRecordImplementor()
    {
        return 'NewsMasterRecord';
    }
}

?>
