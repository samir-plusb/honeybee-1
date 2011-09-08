<?php

/**
 * The ExtendedCouchDbClient is a wrapper around php couchdb pecl library,
 * that extends the latter by composing it and adding in some missing functionality.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         ApplicationBase
 * @subpackage      Database
 */
class ExtendedCouchDbClient
{
    // ---------------------------------- <MEMBERS> ----------------------------------------------
    
    /**
     * Holds a reference to our composed pecl CouchDbClient.
     * 
     * @var         CouchDbClient 
     */
    protected $compositeClient;
    
    /**
     * Holds base uri used to talk to couch db.
     * 
     * @var         string 
     */
    protected $baseUri;
    
    /**
     * Holds a curl handle that is internally used for submitting requests.
     * 
     * @var         Resource
     */
    private $curlHandle = NULL;
    
    // ---------------------------------- </MEMBERS> ---------------------------------------------
    
    
    // ---------------------------------- <CONSTRUCTOR> ------------------------------------------
    
    /**
     * Create a new ExtendedCouchDbClient instance passing in the couchdb base uri.
     * 
     * @param       string $uri 
     */
    public function __construct($uri)
    {
        $this->baseUri = $uri;

        $this->compositeClient = new CouchDbClient($uri);
    }
    
    // ---------------------------------- </CONSTRUCTOR> -----------------------------------------
    
    
    // ---------------------------------- <PUBLIC METHODS> ---------------------------------------
    
    /**
     * Send a batch create/update request to the given couch database
     * and return the resulting response information.
     * 
     * @param       string $database
     * @param       array $documentData
     * 
     * @return      array
     */
    public function storeDocs($database, array $documentData)
    {
        $this->compositeClient->selectDb($database);

        return $this->compositeClient->storeDocs($documentData);
    }
    
    /**
     * Fetch the data for the given $documentId for the passed $database.
     * 
     * @param       string $database
     * @param       string $documentId
     * 
     * @return      array
     */
    public function getDoc($database, $documentId)
    {
        $this->compositeClient->selectDb($database);

        try
        {
            return (array)$this->compositeClient->getDoc($documentId);
        }
        catch(CouchdbClientException $ex)
        {
            return NULL;
        }
    }

    /**
     * Query the given database for revision (e-tag header in response to head request)
     * information on the given $docId.
     * If the document does not exist 0 is returned.
     * 
     * @param       string $database
     * @param       string $docId
     *
     * @return      int Returns the document's revision or 0 if it doesn't exist.
     */
    public function statDoc($database, $docId)
    {
        $curlHandle = $this->getCurlHandle();

        $uri = $this->baseUri . $database . '/' . $docId;
        $file = tmpfile();

        curl_setopt($curlHandle, CURLOPT_URL, $uri);
        curl_setopt($curlHandle, CURLOPT_HEADER, 1);
        curl_setopt($curlHandle, CURLOPT_NOBODY, 1);
        curl_setopt ($curlHandle, CURLOPT_RETURNTRANSFER, 1);

        $resp = curl_exec($curlHandle);
        $this->processCurlErrors($curlHandle);

        fclose($file);

        if (!preg_match_all('~Etag:\s*"(\d+-\w+)"~is', $resp, $matches, PREG_SET_ORDER))
        {
            return 0;
        }

        return $matches[0][1];
    }
    
    /**
     * Fetch the data for the given view.
     * 
     * @param       string $database
     * @param       string $designDocId
     * @param       string $viewname
     * 
     * @return      array
     */
    public function getView($database, $designDocId, $viewname)
    {
        $this->compositeClient->selectDb($database);

        $uri = $this->baseUri . $database . '/_design/' . $designDocId . '/_view/' . $viewname;

        $curlHandle = $this->getCurlHandle();
        curl_setopt($curlHandle, CURLOPT_URL, $uri);

        $resp = curl_exec($curlHandle);
        $this->processCurlErrors($curlHandle);
        $data = json_decode($resp, TRUE);

        return $data;
    }
    
    /**
     * Create a design document for the given $docId.
     * 
     * @param       string $database
     * @param       string $docId
     * @param       array $doc
     * 
     * @return type 
     */
    public function createDesignDocument($database, $docId, array $doc)
    {
        $doc['_id'] = $docId;

        $removeWhitespace = function($funcString)
        {
            return preg_replace('~[\n\r\t]+~', '', $funcString);
        };

        foreach ($doc['views'] as & $view)
        {
            $view['map'] = $removeWhitespace($view['map']);

            if (isset($view['reduce']))
            {
                $view['reduce'] = $removeWhitespace($view['map']);
            }
        }

        $curlHandle = $this->getCurlHandle();

        $uri = $this->baseUri . $database . '/_design/' . $docId;
        $file = tmpfile();
        $jsonDoc = json_encode($doc);
        fwrite($file, $jsonDoc);
        fseek($file, 0);

        curl_setopt($curlHandle, CURLOPT_URL, $uri);
        curl_setopt($curlHandle, CURLOPT_PUT, TRUE);
        curl_setopt($curlHandle, CURLOPT_INFILE, $file);
        curl_setopt($curlHandle, CURLOPT_INFILESIZE, strlen($jsonDoc));

        curl_exec($curlHandle);
        $this->processCurlErrors($curlHandle);

        fclose($file);
    }
    
    /**
     * Create a database by the given $database name.
     * 
     * @param       string $database
     * 
     * @throws      CouchdbClientException e.g. If the database allready exists.
     */
    public function createDatabase($database)
    {
        $this->compositeClient->createDatabase($database);
    }

    /**
     * Delete a database by the given $database name.
     *
     * @param       string $database
     * 
     * @return      void
     * 
     * @throws      CouchdbClientException e.g. database does not exists
     */
    public function deleteDatabase($database)
    {
        $this->compositeClient->deleteDatabase($database);
    }
    
    // ---------------------------------- </PUBLIC METHODS> --------------------------------------
    
    
    // ---------------------------------- <WORKING METHODS> --------------------------------------
    
    /**
     * Returns our curl handle and initializes it upon first invocation.
     *
     * @return      Resource
     */
    protected function getCurlHandle()
    {
        if (! $this->curlHandle)
        {
            $this->curlHandle = $curlHandle = curl_init();

            // @todo introduce an options member for couchdb proxy and verbose and timeout settings.
            curl_setopt($curlHandle, CURLOPT_PROXY, '');
            curl_setopt($curlHandle, CURLOPT_VERBOSE, 0);
            curl_setopt($curlHandle, CURLOPT_TIMEOUT, 10);
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curlHandle, CURLOPT_HEADER, 0);
            curl_setopt($curlHandle, CURLOPT_FAILONERROR, 1);
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curlHandle, CURLOPT_FORBID_REUSE, 0);
            curl_setopt($curlHandle, CURLOPT_FRESH_CONNECT, 0);
            curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, 0);
            curl_setopt($curlHandle, CURLOPT_HEADER, 'Content-Type: application/json; charset=utf-8');
            curl_setopt($curlHandle, CURLOPT_ENCODING, 'gzip,deflate');
        }
        
        return $this->curlHandle;
    }

    /**
     * Check curl response status and throw exception on error.
     *
     * @param       Resource $curlHandle
     * 
     * @return      void
     * 
     * @throws      CouchdbClientException
     */
    protected function processCurlErrors($curlHandle)
    {
        $error = curl_error($curlHandle);
        $errorNum = curl_errno($curlHandle);
        if (200 != curl_getinfo($curlHandle, CURLINFO_HTTP_CODE) || $errorNum || $error)
        {
            throw new CouchdbClientException("CURL error: $error", $errorNum);
        }
    }
    
    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>