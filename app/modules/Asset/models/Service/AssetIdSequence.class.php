<?php

/**
 * The AssetIdSequence class provides an incremental sequence of id's that are unique
 * for the scope of it's current sequence.
 *
 * @version         $Id:$
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
     * @var         ExtendedCouchDbClient
     */
    protected $couchDbClient;

    // ---------------------------------- </MEMBERS> ---------------------------------------------


    // ---------------------------------- <CONSTRUCTOR> ------------------------------------------

    /**
     * Create a new AssetIdSequence instance,
     * thereby initializing our couchdb client.
     */
    public function __construct(ExtendedCouchDbClient $couchClient)
    {
        $this->couchDbClient = $couchClient;
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
        $idSeqDocument = $this->couchDbClient->getDoc(NULL, self::COUCHDB_DOCID);

        if (! $idSeqDocument)
        {
            throw new Exception(
                "The idsequence is an invalid state as it has more than row for the curId view."
            );
        }

        $idSeqDocument['curId']++;
        $idSeqDocument['_id'] = self::COUCHDB_DOCID;
        $this->couchDbClient->storeDoc(NULL, $idSeqDocument);
        return $idSeqDocument['curId'];
    }

    // ---------------------------------- </PUBLIC METHODS> --------------------------------------
}

?>