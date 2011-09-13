<?php

/**
 * The AssetBaseView serves as the base view to all views implemented inside of the Asset module.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Asset
 * @subpackage      Mvc
 */
class AssetBaseView extends ProjectBaseView
{
    /**
     * Return any reported validation error messages from our validation manager.
     * 
     * @return      array 
     */
    protected function getValidationErrorMessages()
    {
        $errors = array();
        
        foreach ($this->getContainer()->getValidationManager()->getErrorMessages() as $errMsg)
        {
            $errors[] = $errMsg['message'];
        }
        
        return $errors;
    }
}

?>