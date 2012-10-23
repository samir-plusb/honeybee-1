<?php

/**
 * The AssetIdSequence class provides an incremental sequence of id's that are unique
 * for the scope of it's current sequence.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Asset
 * @subpackage      Service
 */
class AssetIdSequence
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    /**
     * Holds the name of our coucdb database id for the document we store our ids in.
     */
    const COUCHDB_DOCID = '#__id__sequence__#';

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

    /**
     * Create a new AssetIdSequence instance,
     * thereby initializing our couchdb client.
     */
    public function __construct(CouchDocumentStore $documentStore)
    {
        $this->documentStore = $documentStore;
    }

    // ---------------------------------- </CONSTRUCTOR> -----------------------------------------


    // ---------------------------------- <PUBLIC METHODS> ---------------------------------------

    /**
     * Return the next id from our sequence,
     * thereby incrementing the current one.
     *
     * @return      int
     */
    public function nextId()
    {
        $currentId = $this->documentStore->fetchByIdentifier(self::COUCHDB_DOCID);
        if (! $currentId)
        {
            throw new Exception(
                "The idsequence is an invalid as the '" . self::COUCHDB_DOCID . "' document does not exist or exists more than once."
            );
        }
        $nextId = $currentId->increment();
        $this->documentStore->save($currentId);
        return $nextId;
    }

    // ---------------------------------- </PUBLIC METHODS> --------------------------------------
}

?>