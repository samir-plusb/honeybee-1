<?php

/**
 * Handler to access WorkflowItems in the database
 *
 * @package Workflow
 * @author tay
 * @version $Id$
 * @since 25.10.2011
 */
class WorkflowItemPeer
{
    /**
     * Holds the couchdb client that we use to persist
     * and retrieve
     *
     * @var ExtendedCouchDbClient
     */
    protected $client;

    /**
     * constuct a handler instance
     *
     * The Workflow handler should be instantiated using the factory method in the supervisor.
     *
     * @see Workflow_SupervisorModel::getItemPeer()
     */
    public function __construct(ExtendedCouchDbClient $client)
    {
        $this->client = $client;
    }

    /**
     * get the database handler for the import items database
     *
     * @return ExtendedCouchDbClient
     */
    public function getDatabase()
    {
        return $this->client;
    }

    /**
     * Get a workflow item by its document id (identifier).
     *
     * @param string $identifier
     *
     * @return IWorkflowItem
     *
     * @throws CouchdbClientException
     */
    public function getItemByIdentifier($identifier)
    {
        $item = NULL;
        try
        {
            $data = $this->client->getDoc(NULL, $identifier);
            $item = new WorkflowItem($data);
        }
        catch(CouchdbClientException $e)
        {
            if (preg_match('~(\(404\))~', $e->getMessage()))
            {
                $item = NULL;
            }
            else
            {
                throw $e;
            }
        }
        return $item;
    }

    /**
     * Store the given workflow item to the database.
     *
     * @param IWorkflowItem $item
     *
     * @throws CouchdbClientException
     */
    public function storeItem(IWorkflowItem $item)
    {
        $item->touch();
        $document = $item->toArray();
        unset($document['identifier']);
        $document['_id'] = $item->getIdentifier();
        $document['type'] = get_class($item);
        if (isset($document['revision']))
        {
            $document['_rev'] = $document['revision'];
        }
        unset($document['revision']);
        $result = $this->client->storeDoc(NULL, $document);
        if (isset($result['ok']))
        {
            $item->bumpRevision($result['rev']);
            return TRUE;
        }
        return FALSE;
    }

    public function deleteItem(IWorkflowItem $item)
    {
        $document = $item->toArray();
        $result = $this->client->deleteDoc(NULL, $item->getIdentifier(), $document['revision']);
        if (isset($result['ok']))
        {
            return TRUE;
        }
        return FALSE;
    }
}