<?php

class ImperiaDataSource extends ImportBaseDataSource
{
    const URl_PATH_LOGIN = "/cgi-bin/site_login.pl";

    const URl_PATH_EXPORT = "/cgi-bin/xml_dump.pl?node_id=%s";

    const INPUT_USERNAME = 'my_imperia_login';

    const INPUT_PASSWORD = 'my_imperia_pass';

    private $documentIds;

    private $curlHandle;

    private $cookiePath;

    private $cursorPos;

    public function __construct(IImportConfig $config)
    {
        parent::__construct($config);

        $this->documentIds = $this->config->getSetting(ImperiaDataSourceConfig::CFG_DOCUMENT_IDS);
    }

    protected function init()
    {
        $this->initCurlHandle();
        $this->login();

        $this->cursorPos = -1;
    }

    protected function forwardCursor()
    {
        if ($this->cursorPos < count($this->documentIds) - 1)
        {
            $this->cursorPos++;

            return TRUE;
        }

        return FALSE;
    }

    protected function createRecord()
    {
        $docId = $this->documentIds[$this->cursorPos];
        $documentXml = $this->loadDocumentById($docId);
        
        $recordClass = $this->config->getSetting(ImperiaDataSourceConfig::CFG_RECORD_TYPE);
        
        if (!class_exists($recordClass, true))
        {
            throw new DataSourceException(
                sprintf(
                    "Unable to find provided datarecord class: %s",
                    $recordClass
                )
            );
        }
        
        $record = new $recordClass($documentXml);
        
        if (!($record instanceof IDataRecord))
        {
            throw new DataSourceException(
                sprintf(
                    "An invalid IDataRecord implementor was provided. '%s' does not implement the interface IDataRecord.",
                    $recordClass
                )
            );
        }
        
        return $record;
    }

    protected function initCurlHandle()
    {
        if (!isset($this->curlHandle))
        {
            $this->cookiePath = tempnam(sys_get_temp_dir(), 'imperia.datasrc.');

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookiePath);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookiePath);
            curl_setopt($ch, CURLOPT_FORBID_REUSE, 0);
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);

            $this->curlHandle = $ch;
        }

    }

    protected function loadDocumentById($documentId)
    {
        $docExportUrl = $this->buildDocExportUrlById($documentId);

        curl_setopt($this->curlHandle, CURLOPT_URL, $docExportUrl);
        curl_setopt($this->curlHandle, CURLOPT_HTTPGET, 1);

        $responseDoc = curl_exec($this->curlHandle);
        $err = curl_error($this->curlHandle);
        $errNo = curl_errno($this->curlHandle);
        $respCode = curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE);

        if ($err || $errNo || 200 != $respCode)
        {
            $msg = sprintf("An error occured while loading: %s Error: %s, Resp-code: %s", $docExportUrl, $err, $respCode);
            throw new DataSourceException($msg, $errNo);
        }

        if (FALSE !== strpos($responseDoc, '<title>Access Denied!</title>'))
        {
            throw new DataSourceException("Currently not logged in to imperia and therefor can not continue.");
        }

        return $responseDoc;
    }

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
            $msg = sprintf("Can not login to imperia. Error: %s, Resp-code: %d", $err, $respCode);
            throw new DataSourceException($msg, $errNo);
        }
    }

    protected function buildDocExportUrlById($documentId)
    {
        $baseUrl = $this->config->getSetting(ImperiaDataSourceConfig::CFG_URL);

        return $baseUrl . sprintf(self::URl_PATH_EXPORT, $documentId);
    }

    protected function buildLoginUrl()
    {
        $baseUrl = $this->config->getSetting(ImperiaDataSourceConfig::CFG_URL);

        return $baseUrl . self::URl_PATH_LOGIN;
    }
}

?>
