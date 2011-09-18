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
    public function __construct(IImportConfig $config, $name, $description = NULL)
    {
        parent::__construct($config, $name, $description);

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

            $this->curlHandle = ProjectCurl::create();
            curl_setopt($this->curlHandle, CURLOPT_POST, 0);
            curl_setopt($this->curlHandle, CURLOPT_COOKIEFILE, $this->cookiePath);
            curl_setopt($this->curlHandle, CURLOPT_COOKIEJAR, $this->cookiePath);
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
        
        $this->processCurlErrors(
            "An error occured while trying to load doc-idlist from: $idListUrl"
        );

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
        
        $this->checkImperiaSessionState(
            $responseDoc,
            "An error occured while loading: $idListUrl"
         );
        
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
        $this->checkImperiaSessionState($resp, "Unable to login to imperia.");
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
    
    /**
     * Checks the state of a given imperia response right after it has
     * been received and if our session is still valid, hence we are logged on
     * and can continue work.
     * To do this we first check our curl handle for errors
     * and then check if imperia threw it's login screen at us.
     * 
     * @param       string $resp
     * @param       string $errMsg 
     */
    protected function checkImperiaSessionState($resp, $errMsg = '')
    {
        $this->processCurlErrors($errMsg);
        
        // Is there a better way to find out that imperia didn't let us in?
        if (FALSE !== strpos($resp, '<title>Access Denied!</title>'))
        {
            $errMsg = 'Error: The datasource is not logged in. ' . PHP_EOL . $errMsg;
            
            throw new DataSourceException($errMsg);
        }
    }
    
    /**
     * Check curl response status and throw exception on error.
     *
     * @param       string $msg
     *
     * @return      void
     *
     * @throws      CouchdbClientException
     */
    protected function processCurlErrors($msg = '')
    {
        $error = curl_error($this->curlHandle);
        $errorNum = curl_errno($this->curlHandle);
        $respCode = curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE);

        if (200 > $respCode || 300 <= $respCode || $errorNum || $error)
        {
            $msg = $msg . PHP_EOL . 'Curl Error: ' . PHP_EOL . $error;
            
            throw new DataSourceException($msg, $errorNum);
        }
    }

    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>