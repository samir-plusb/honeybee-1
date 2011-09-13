<?php

/**
 * The Asset_SetupAction is responseable for setting up our module for usage.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Asset
 * @subpackage      Mvc
 */
class Asset_SetupAction extends AssetBaseAction
{
    /**
     * Execute the write logic for this action, hence process the given asset.
     * 
     * @param       AgaviRequestDataHolder $parameters
     * 
     * @return      string The name of the view to execute.
     * 
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeWrite(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $moduleSetup = new AssetModuleSetup();

        try
        {
            $moduleSetup->setup(TRUE);
        }
        catch (Exception $e)
        {
            throw $e;
            $this->setAttribute('errors', array($e->getMessage()));

            return 'Error';
        }

        return 'Success';
    }

}

?>