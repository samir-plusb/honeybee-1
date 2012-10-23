<?php

class ProjectIdSequence
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    /**
     * Holds the name of our coucdb database id for the document we store our ids in.
     */
    const COUCHDB_DOCID = '[_id_sequence_]';

    // ---------------------------------- </CONSTANTS> -------------------------------------------


    // ---------------------------------- <MEMBERS> ----------------------------------------------

    /**
     * Holds the client we use to talk to couchdb.
     *
     * @var         CouchDocumentStore
     */
    protected $documentStore;

    // ---------------------------------- </MEMBERS> ---------------------------------------------


    // ---------------------------------- <CONSTRUCTOR> ------------------------------------------

    public function __construct($moduleTypeId)
    {
        $supervisor = WorkflowSupervisorFactory::createByTypeKey($moduleTypeId);
        $this->documentStore = $supervisor->getWorkflowItemStore();
    }

    // ---------------------------------- </CONSTRUCTOR> -----------------------------------------


    // ---------------------------------- <PUBLIC METHODS> ---------------------------------------

    /**
     * Return the next id from our sequence,
     * thereby incrementing the current one.
     *
     * @return      int
     */
    public function nextId($receipient)
    {
        $idSequenceMap = $this->documentStore->fetchByIdentifier(self::COUCHDB_DOCID);
        if (NULL === $idSequenceMap)
        {
            $idSequenceMap = ProjectIdSequenceMap::fromArray(array(
                'identifier' => self::COUCHDB_DOCID,
                'ids' => array()
            ));
        }
        $nextId = $idSequenceMap->addId($receipient);
        $this->documentStore->save($idSequenceMap);
        return $nextId;
    }

    // ---------------------------------- </PUBLIC METHODS> --------------------------------------
}
