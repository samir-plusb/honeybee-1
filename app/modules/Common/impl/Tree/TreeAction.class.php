<?php

/**
 * The Common_TreeAction is repsonseable for rendering tree data in a reusable way :).
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Leon Weidauer <leon.weidauer@gmail.com>
 * @package         Common
 * @subpackage      Mvc
 */
class Common_TreeAction extends CommonBaseAction
{
    const PATH_DATA_PREFIX = 'data';
    
    /**
     * Execute the read logic for this action, hence load our news items.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @return      string The name of the view to execute.
     */
    public function executeRead(AgaviRequestDataHolder $parameters)
    {
        return 'Success';
    }
}
