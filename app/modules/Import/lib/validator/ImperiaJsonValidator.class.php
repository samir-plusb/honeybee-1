<?php

/**
 * The ImperiaJsonValidator class provides validation of json data sent by imperia.
 *
 * @version         $Id: $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Validation
 */
class ImperiaJsonValidator extends AgaviStringValidator
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------
    
    /**
     * Holds the name of the error thrown when invalid json data is encountered.
     */
    const ERR_INVALID_JSON = 'invalid_json';
    
    /**
     * Holds the name of the parameter than can be used to set the name of the request-data field,
     * that we shall export the vaidated data to.
     */
    const PARAM_EXPORT = 'export';
    
    /**
     * Holds the name of the default request-data field used to export data to,
     * when no self::PARAM_EXPORT has been provided.
     */
    const DEFAULT_PARAM_EXPORT = 'ids';
    
    // ---------------------------------- </CONSTANTS> -------------------------------------------
    
    
    // ---------------------------------- <AgaviStringValidator OVERRIDES> -----------------------
    
    /**
     * Validate that the given data is valid json and export the decoded value.
     * 
     * @return      boolean
     */
    protected function validate()
    {
        if (!parent::validate())
        {
            return FALSE;
        }
        
        $jsonString = $this->getData($this->getArgument());
        $data = NULL;
        
        if (!($data = @json_decode($jsonString, TRUE)))
        {
            $this->throwError(self::ERR_INVALID_JSON);
        }
        
        $this->export($data, $this->getParameter(self::PARAM_EXPORT, self::DEFAULT_PARAM_EXPORT));
        
        return TRUE;
    }
    
    // ---------------------------------- </AgaviStringValidator OVERRIDES> ----------------------
}

?>