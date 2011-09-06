<?php

/**
 * IUriContainer implementations provide access to the different parts of agiven uri.
 * This interface is used in the context of loading resources for IImportConfig implementations.
 * 
 * @copyright   BerlinOnline Stadtportal GmbH & Co. KG
 * @author      Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package     Import/Base
 * @subpackage  Config
 */
interface IUriContainer
{
    /**
     * Return an array reflecting our parsed uri.
     * 
     * @return array
     */
    public function getUriParts();
    
    /**
     * Returns the IUriContainer's original uri.
     * 
     * @return string
     */
    public function getUri();
}

?>