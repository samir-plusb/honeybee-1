<?php

/**
 * IUriContainer implementations provide access to the different parts of agiven uri.
 * This interface is used in the context of loading resources for IImportConfig implementations.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Base
 */
interface IUriContainer
{
    /**
     * Return an array reflecting our parsed uri.
     *
     * @return      array
     */
    public function getUriParts();

    /**
     * Returns the IUriContainer's original uri.
     *
     * @return      string
     */
    public function getUri();
}

?>