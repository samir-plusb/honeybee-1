<?php

class GoogleDocsService
{
    const WORKSHEET_NUMBER = 1;

    const LANG_EN = 'en';

    const FIELD_LOCALE = 'locale';

    private $email;

    private $password;

    protected $googleApiClient;

    public function __construct($email, $password)
    {
        $this->loadRequiredZendClasses();
        $this->email = $email;
        $this->password = $password;
    }

    public function getDocumentData($documentId)
    {
        $documentData = array();
        
        $spreadsheetService = new Zend_Gdata_Spreadsheets(
            $this->getGoogleApiClient()
        );
        $query = new Zend_Gdata_Spreadsheets_ListQuery();
        $query->setSpreadsheetKey($documentId);
        $query->setWorksheetId(self::WORKSHEET_NUMBER);
        
        $listFeed = $spreadsheetService->getListFeed($query);
        foreach($listFeed->entries as $row) 
        {
            $rowData = $row->getCustom();
            $localeCell = $row->getCustomByName(self::FIELD_LOCALE);
            if (self::LANG_EN == $localeCell->getText())
            {
                continue;
            }
            foreach($rowData as $customEntry) 
            {
                $outRow[$customEntry->getColumnName()] = $customEntry->getText();
            }
            $documentData[] = $outRow;
        }
        
        return $documentData;
    }

    protected function getGoogleApiClient()
    {
        if (! $this->googleApiClient)
        {
            $this->googleApiClient = $this->createGoogleApiClient();
        }
        return $this->googleApiClient;
    }

    protected function createGoogleApiClient()
    {
        try
        {
            return Zend_Gdata_ClientLogin::getHttpClient($this->email, $this->password, 
                Zend_Gdata_Spreadsheets::AUTH_SERVICE_NAME
            );
        }
        catch (Zend_Gdata_App_AuthException $ae)
        {
            error_log("Failed to init googledocs client:\n" . $ae->getMessage());
            throw new Exception("An unexpected error occured while creating googledocs client.", 0, $ae);
        }
    }

    protected function loadRequiredZendClasses()
    {
        require_once 'Zend/Loader.php';
        Zend_Loader::loadClass('Zend_Gdata');
        Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
        Zend_Loader::loadClass('Zend_Gdata_Spreadsheets');
        Zend_Loader::loadClass('Zend_Gdata_App_AuthException');
        Zend_Loader::loadClass('Zend_Http_Client');
        Zend_Loader::loadClass('Zend_Http_Client_Adapter_ProxyWithCurl');
        Zend_Loader::loadClass('Zend_GdataSpreadsheetsExport');
    }
}

?>

