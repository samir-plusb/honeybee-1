<?php

/**
 * The Common_SidebarAction is repsonseable for rendering the sidebar container and managing its content slots.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Leon Weidauer <leon.weidauer@gmail.com>
 * @package         Common
 * @subpackage      Mvc
 */
class Common_SidebarAction extends CommonBaseAction
{
    /**
     * Execute the read logic for this action, hence prompt for an asset.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @return      string The name of the view to execute.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeRead(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        return 'Success';
    }

    public function handleReadError(AgaviRequestDataHolder $parameters)
    {
        var_dump($this->getContainer()->getValidationManager()->getErrorMessages());exit;
    }
}
