<?php

class ExtendedCouchDbClient
{
    protected $compositeClient;

    protected $baseUri;

    public function __construct($uri)
    {
        $this->baseUri = $uri;

        $this->compositeClient = new CouchDbClient($uri);
    }

    public function storeDocs($database, $documentData)
    {
        $this->compositeClient->selectDb($database);
        
        return $this->compositeClient->storeDocs($documentData);
    }

    public function getDoc($database, $documentId)
    {
        $this->compositeClient->selectDb($database);

        try
        {
            return (array) $this->compositeClient->getDoc($documentId);
        }
        catch(CouchdbClientException $ex)
        {
            return null;
        }
    }
    
    /**
     *
     * @param type $database
     * @param type $docId
     * 
     * @return int Returns the document's revision or 0 if it doesn't exist.
     */
    public function statDoc($database, $docId)
    {
        $ch = self::createCurlHandle();

        $uri = $this->baseUri . $database . '/' . $docId;
        $file = tmpfile();

        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);

        $resp = curl_exec($ch);
        $error = curl_error($ch);
        $errorNum = curl_errno($ch);

        if ($errorNum || $error)
        {
            throw new Exception("An unexpected error occured: $error", $errorNum);
        }

        fclose($file);
        curl_close($ch);
        
        if (!preg_match_all('~Etag:\s*"(\d+-\w+)"~is', $resp, $matches, PREG_SET_ORDER))
        {
            return 0;
        }
        
        return $matches[0][1];
    }

    public function getView($database, $designDocId, $viewname)
    {
        $this->compositeClient->selectDb($database);

        $uri = $this->baseUri . $database . '/_design/' . $designDocId . '/_view/' . $viewname;

        $ch = self::createCurlHandle();
        curl_setopt($ch, CURLOPT_URL, $uri);

        $resp = curl_exec($ch);
        $error = curl_error($ch);
        $errorNum = curl_errno($ch);

        if ($errorNum || $error)
        {
            throw new Exception("An unexpected error occured: $error", $errorNum);
        }

        $data = json_decode($resp, true);

        curl_close($ch);

        return $data;
    }

    public function createDesignDocument($database, $docId, $doc)
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

        $ch = self::createCurlHandle();

        $uri = $this->baseUri . $database . '/_design/' . $docId;
        $file = tmpfile();
        $jsonDoc = json_encode($doc);
        fwrite($file, $jsonDoc);
        fseek($file, 0);

        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_PUT, true);
        curl_setopt($ch, CURLOPT_INFILE, $file);
        curl_setopt($ch, CURLOPT_INFILESIZE, strlen($jsonDoc));

        $resp = curl_exec($ch);
        $error = curl_error($ch);
        $errorNum = curl_errno($ch);

        if ($errorNum || $error)
        {
            throw new Exception("An unexpected error occured: $error", $errorNum);
        }

        fclose($file);
        curl_close($ch);
    }

    public function createDatabase($database)
    {
        $this->compositeClient->createDatabase($database);
    }
    
    public function deleteDatabase($database)
    {
        $this->compositeClient->deleteDatabase($database);
    }

    public static function createCurlHandle()
    {
        $curlHandle = curl_init();

        // @todo introduce an options member for couchdb proxy and verbose and timeout settings.
        curl_setopt($curlHandle, CURLOPT_PROXY, '');
        curl_setopt($curlHandle, CURLOPT_VERBOSE, 0);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT, 10);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_HEADER, 0);
        curl_setopt($curlHandle, CURLOPT_FAILONERROR, 1);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curlHandle, CURLOPT_HEADER, 'Content-Type: application/json; charset=utf-8');
        curl_setopt($curlHandle, CURLOPT_ENCODING, 'gzip,deflate');

        return $curlHandle;
    }
}
