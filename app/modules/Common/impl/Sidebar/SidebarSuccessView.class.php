<?php

/**
 * The Common_Sidebar_SidebarSuccessView class handles Common/Sidebar success data presentation.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Leon Weidauer <leon.weidauer@gmail.com>
 * @package         Common
 * @subpackage      Mvc
 */
class Common_Sidebar_SidebarSuccessView extends CommonBaseView
{
    /**
     * Handle presentation logic for the web  (html).
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeHtml(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $this->setupHtml($parameters);
        $tree_modules = $parameters->getParameter('tree_modules');

        foreach($tree_modules as $moduleName)
        {
            $this->getLayer('content')->setSlot(
                $moduleName,
                $this->createSlotContainer('Common', 'SidebarTree', array(
                    'moduleName' => $moduleName
                ), NULL, 'read')
            );
        }
    }

}

