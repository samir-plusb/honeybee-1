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
class ImperiaJsonValidator extends AgaviValidator
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    /**
     * Holds the name of the error thrown when invalid json data is encountered.
     */
    const ERR_INVALID_JSON = 'invalid_json';
    /**
     * raised if json does not contains a root level array
     */
    const ERR_INVALID_STRUCTURE = 'invalid_structure';
    /**
     * raised if any item does not contain a valid '__imperia_node_id' key
     */
    const ERR_INVALID_NODEID = 'invalid_nodeid';
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
    
    /**
     * Holds the array key in our json data array that holds a specific imperia node id.
     */
    const IMPERIA_NODE_ID_FIELD = '__imperia_node_id';

    // ---------------------------------- </CONSTANTS> -------------------------------------------


    // ---------------------------------- <AgaviStringValidator OVERRIDES> -----------------------

    /**
     * Validate that the given data is valid json and export the decoded value.
     *
     * @return      boolean
     */
    protected function validate()
    {
        $jsonString = $this->getData($this->getArgument());
        if (! is_scalar($jsonString))
        {
            $this->throwError(self::ERR_INVALID_JSON);
            
            return FALSE;
        }

        $data = @json_decode($jsonString, TRUE);
        if (NULL === $data)
        {
            $this->throwError(self::ERR_INVALID_JSON);
        }

        if (! is_array($data))
        {
            $this->throwError(self::ERR_INVALID_STRUCTURE);
            return FALSE;
        }

        foreach ($data as $info)
        {
            $imperiaNodeId = isset($info[self::IMPERIA_NODE_ID_FIELD]) 
                ? $info[self::IMPERIA_NODE_ID_FIELD] 
                : '';
            
            if (! preg_match('#^/\d+[\d/]+$#', $imperiaNodeId))
            {
                $this->throwError(self::ERR_INVALID_NODEID);
                return FALSE;
            }
        }

        $this->export($data, $this->getParameter(self::PARAM_EXPORT, self::DEFAULT_PARAM_EXPORT));

        return TRUE;
    }

    // ---------------------------------- </AgaviStringValidator OVERRIDES> ----------------------
}

?>