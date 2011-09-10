<?php

/**
 * The ImperiaDataSource class is a concrete implementation of the ImportBaseDataSource base class.
 * It provides fetching xml based data from a given imperia export url.
 * 
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Imperia
 */
class ImperiaDataSource extends ImportBaseDataSource
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------
    
    /**
     * Holds an imperia url path, that is relative to it's base uri,
     * pointing to the default login location.
     */
    const URL_PATH_LOGIN = "cgi-bin/site_login.pl";
    
    /**
     * Holds an imperia url path, that is relative to it's base uri,
     * pointing to the default xml export location.
     */
    const URL_PATH_EXPORT = "cgi-bin/xml_dump.pl?node_id=%s";
    
    /**
     * Holds the name of imperia's login postfield for the account username.
     */
    const INPUT_USERNAME = 'my_imperia_login';
    
    /**
     * Holds the name of imperia's login postfield for the account password.
     */
    const INPUT_PASSWORD = 'my_imperia_pass';
    
    // ---------------------------------- </CONSTANTS> -------------------------------------------
    
    
    // ---------------------------------- <MEMBERS> ----------------------------------------------
    
    /**
     * Holds an array with imperia document ids,
     * that shall be fetched during iteration.
     * These are either pulled from the imperia update api
     * or statically passed during some of the class's tests.
     * 
     * @var         array 
     */
    private $documentIds;
    
    /**
     * Holds a reference to a curl handle that we use for 
     * submitting requests to imperia.
     * 
     * @var         Resource 
     */
    private $curlHandle;
    
    /**
     * Holds a file system path pointing to the cookie,
     * that we use to keep our imperia login session alive.
     * 
     * @var         string
     */
    private $cookiePath;
    
    /**
     * Holds our current position, 
     * while iterating over our $documentIds.
     * 
     * @var         int
     */
    private $cursorPos;
    
    // ---------------------------------- </MEMBERS> ---------------------------------------------
    
    
    // ---------------------------------- <ImportBaseDataSource OVERRIDES> -----------------------
    
    /**
     * Create a new ImperiaDataSource instance.
     * 
     * @param       IImportConfig $config 
     * 
     * @see         ImportBaseDataSource::__construct()
     */
    public function __construct(IImportConfig $config)
    {
        parent::__construct($config);

        $this->documentIds = $this->config->getSetting(
            ImperiaDataSourceConfig::PARAM_DOCIDS
        );
    }
    
    // ---------------------------------- </ImportBaseDataSource OVERRIDES> ----------------------
    
    
    // ---------------------------------- <ImportBaseDataSource IMPL> ----------------------------
    
    /**
     * Initialize our datasource.
     * 
     * @see         ImportBaseDataSource::init()
     * 
     * @uses        ImperiaDataSource::initCurlHandle()
     * @uses        ImperiaDataSource::loadDocumentIds()
     * @uses        ImperiaDataSource::login()
     */
    protected function init()
    {
        $this->initCurlHandle();

        if (empty($this->documentIds))
        {
            $this->loadDocumentIds();
        }

        $this->login();

        $this->cursorPos = -1;
    }
    
    /**
     * Forward our cursor, hence move to our next $documentId.
     * 
     * @return      boolean
     * 
     * @see         ImportBaseDataSource::forwardCursor()
     */
    protected function forwardCursor()
    {
        if ($this->cursorPos < count($this->documentIds) - 1)
        {
            $this->cursorPos++;

            return TRUE;
        }

        return FALSE;
    }
    
    /**
     * Forward our cursor, hence move to our next $documentId.
     * 
     * @return      array
     * 
     * @see         ImportBaseDataSource::fetchData()
     * 
     * @uses        ImperiaDataSource::loadDocumentById()
     */
    protected function fetchData()
    {
        $docId = $this->documentIds[$this->cursorPos];
        $documentXml = $this->loadDocumentById($docId);

        return $documentXml;
    }
    
    // ---------------------------------- </ImportBaseDataSource IMPL> ---------------------------
    
    
    // ---------------------------------- <WORKING METHODS> -------------------------------------.
    
    /**
     * Initialize our curl handle.
     */
    protected function initCurlHandle()
    {
        if (!isset($this->curlHandle))
        {
            $this->cookiePath = tempnam(sys_get_temp_dir(), uniqid('imperia.'));

            $this->curlHandle = curl_init();
            curl_setopt($this->curlHandle, CURLOPT_POST, 0);
            curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($this->curlHandle, CURLOPT_TIMEOUT, 10);
            curl_setopt($this->curlHandle, CURLOPT_COOKIEFILE, $this->cookiePath);
            curl_setopt($this->curlHandle, CURLOPT_COOKIEJAR, $this->cookiePath);
            curl_setopt($this->curlHandle, CURLOPT_FORBID_REUSE, 0);
            curl_setopt($this->curlHandle, CURLOPT_FRESH_CONNECT, 0);
            curl_setopt($this->curlHandle, CURLOPT_FOLLOWLOCATION, 0);
        }
    }
    
    /**
     * Fetch a list of document ids to import 
     * from the imperia update-stream service-api.
     * 
     * @return      array
     * 
     * @throws      DataSourceException
     */
    protected function loadDocumentIds()
    {
        $this->documentIds = array();
        
        $idListUrl = $this->config->getSetting(ImperiaDataSourceConfig::CFG_DOC_IDLIST_URL);
        
        curl_setopt($this->curlHandle, CURLOPT_URL, $idListUrl);
        curl_setopt($this->curlHandle, CURLOPT_HTTPGET, 1);

        $response = curl_exec($this->curlHandle);
        $err = curl_error($this->curlHandle);
        $errNo = curl_errno($this->curlHandle);
        $respCode = curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE);

        if ($err || $errNo || 200 != $respCode)
        {
            $msg = sprintf(
                "An error occured while trying to load doc-idlist from: %s Error: %s, Resp-code: %s",
                $idListUrl,
                $err,
                $respCode
            );
            
            throw new DataSourceException($msg, $errNo);
        }
        
        if (!empty($response))
        {
            $this->documentIds = explode(' ', trim($response));
        }
    }
    
    /**
     * Load an imperia document (xml-string) for the given imperia node-id
     * from our configured imperia export-url.
     * 
     * @param       string $documentId
     * 
     * @return      string 
     * 
     * @throws      DataSourceException If we are not logged in or the document request fails.
     * 
     * @uses        ImperiaDataSource::buildDocExportUrlById()
     */
    protected function loadDocumentById($documentId)
    {
        $idListUrl = $this->buildDocExportUrlById($documentId);

        curl_setopt($this->curlHandle, CURLOPT_URL, $idListUrl);
        curl_setopt($this->curlHandle, CURLOPT_HTTPGET, 1);

        $responseDoc = curl_exec($this->curlHandle);
        $err = curl_error($this->curlHandle);
        $errNo = curl_errno($this->curlHandle);
        $respCode = curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE);

        if ($err || $errNo || 200 != $respCode)
        {
            $msg = sprintf(
                "An error occured while loading: %s Error: %s, Resp-code: %s",
                $idListUrl,
                $err,
                $respCode
            );
            
            throw new DataSourceException($msg, $errNo);
        }

        if (FALSE !== strpos($responseDoc, '<title>Access Denied!</title>'))
        {
            throw new DataSourceException(
                "Currently not logged in to imperia and therefore can not continue."
            );
        }

        return $responseDoc;
    }
    
    /**
     * Try and login to imperia with our configured account data.
     * 
     * @throws      DataSourceException If we can't manage to login.
     * 
     * @uses        ImperiaDataSource::buildLoginUrl()
     */
    protected function login()
    {
        $post = array(
            self::INPUT_USERNAME => $this->config->getSetting(
                ImperiaDataSourceConfig::CFG_ACCOUNT_USER
            ),
            self::INPUT_PASSWORD => $this->config->getSetting(
                ImperiaDataSourceConfig::CFG_ACCOUNT_PASS
            )
        );

        curl_setopt($this->curlHandle, CURLOPT_POST, 1);
        curl_setopt($this->curlHandle, CURLOPT_POSTFIELDS, http_build_query($post));
        curl_setopt($this->curlHandle, CURLOPT_URL, $this->buildLoginUrl());

        $resp = curl_exec($this->curlHandle);
        $err = curl_error($this->curlHandle);
        $errNo = curl_errno($this->curlHandle);
        $respCode = curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE);

        if ($err || $errNo || 200 != $respCode || FALSE !== strpos($resp, '<title>Access Denied!</title>'))
        {
            $msg = sprintf(
                "Can not login to imperia. Error: %s, Resp-code: %d",
                $err,
                $respCode
            );
            
            throw new DataSourceException($msg, $errNo);
        }
    }
    
    /**
     * Build an imperia export url for the given document id
     * relative to the configured imperia baseurl.
     * 
     * @param       string $documentId
     * 
     * @return      string 
     */
    protected function buildDocExportUrlById($documentId)
    {
        $baseUrl = $this->config->getSetting(ImperiaDataSourceConfig::CFG_URL);

        return $baseUrl . sprintf(self::URL_PATH_EXPORT, $documentId);
    }
    
    /**
     * Build an imperia login url relative to the configured imperia baseurl.
     * 
     * @return      string 
     */
    protected function buildLoginUrl()
    {
        $baseUrl = $this->config->getSetting(ImperiaDataSourceConfig::CFG_URL);

        return $baseUrl . self::URL_PATH_LOGIN;
    }
    
    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>