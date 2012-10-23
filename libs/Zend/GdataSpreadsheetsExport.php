<?php 
/**
 * Helper class for export google docs spreadsheets with Zend GData
 * supported formats xls,csv,pdf,ods,tsv,html 
 *  
 * @version 1.0
 * @author Toni Blonske
 */
class Zend_GdataSpreadsheetsExport extends Zend_Gdata_Spreadsheets
{
    /**
     * the google docs spreadsheets url
     *              
     * @var $exportUrl string
     */         
    private $exportUrl = 'https://spreadsheets.google.com/feeds/download/spreadsheets/Export?key={key}&exportFormat=';
    
    /**
     * the valid export formats 
     *              
     * @var $validExportFormats string
     */ 
    private $validExportFormats = 'xls|csv|pdf|ods|tsv|html';
    
    /**
     * Create Gdata_Spreadsheets object
     *
     * @param Zend_Http_Client $client (optional) The HTTP client to use when
     *                                  when communicating with the Google servers.
     * @param string $applicationId The identity of the app in the form of Company-AppName-Version
     */     
    public function __construct($client = null, $applicationId = 'MyCompany-MyApp-1.0')
    {
         parent::__construct($client, $applicationId);
    }
    
    /**
     * GET a URI using client object.
     *
     * @param  string $uri GET URI
     * @param  array $extraHeaders Extra headers to add to the request, as an
     *         array of string-based key/value pairs.
     * @throws Zend_Gdata_App_HttpException
     * @return string the response
     */
    public function getBodyWithoutHeaders($url, $extraHeaders = array())
    {
        $response = $this->get($url, $extraHeaders);
        return $response->extractBody($response->getBody());
    }
    
    /**
     * magic method __call to export the spreadsheet using getXls || getHtml || getCsv
     * 
     * @param  string $method the methodname
     * @param  array $arguments the arguments argument #1 contains the doc id, 
     *                                        argument #2 [optional] contains extra headers for the Zend request  
     * @return string 
     * @throws Zend_GdataSpreadsheetsExportException IF invalid methed or missing arguments                    
     */              
    public function __call($method, $arguments)
    {
        $exceptionStart = get_class($this) . '::' . $method;
        
        if (sizeof($arguments) == 0) 
        {
            throw new Zend_GdataSpreadsheetsExportException($exceptionStart . ' argument #1 not found');
        }

        if (preg_match('/^get(' . $this->validExportFormats . ')/i', strtolower($method), $matches)) 
        {
            $exportFormat = $matches[1];
            $docId        = $arguments[0];
            $extraHeaders = isset($arguments[1]) ? $arguments[1] :  array();
            
            $exportUrl = str_replace('{key}', $docId, $this->exportUrl) . $exportFormat;
            return $this->getBodyWithoutHeaders($exportUrl, $extraHeaders);
        }
        else
        {
            throw new Zend_GdataSpreadsheetsExportException($exceptionStart . ' invalid method');
        }
        
        return false;
    }
}

class Zend_GdataSpreadsheetsExportException extends Exception {}
?>