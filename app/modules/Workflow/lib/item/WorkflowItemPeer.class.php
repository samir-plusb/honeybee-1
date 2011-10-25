<?php

/**
 * Handler to access WorkflowItems in the database
 *
 * @package Workflow
 * @author tay
 * @version $Id$
 * @since 25.10.2011
 *
 */
class WorkflowItemPeer
{
    /**
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
    public function __construct()
    {
        $this->client = AgaviContext::getInstance()->getDatabaseConnection(ItemsModuleSetup::COUCHDB_DATABASE);
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
     * get a import item by its document id
     *
     * @param string $documentId
     *
     * @return WorkflowItem
     *
     * @throws CouchdbClientException
     */
    public function getItemByIdentifier($documentId)
    {
        $data = $this->client->getDoc(NULL, $documentId);
        return new WorkflowItem($data);
    }
}