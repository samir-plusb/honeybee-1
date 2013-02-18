<?php

/**
 * The Common_Tree_TreeSuccessView class handles Common/Tree success data presentation.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Leon Weidauer <leon.weidauer@gmail.com>
 * @package         Common
 * @subpackage      Mvc
 */
class Common_SidebarTree_SidebarTreeSuccessView extends CommonBaseView
{
    public function executeHtml(\AgaviRequestDataHolder $parameters)
    {
        $this->setupHtml($parameters);
        $moduleClassName = $parameters->getParameter('moduleName');
        $module = $moduleClassName::getInstance();
        $tree = $module->getService('tree')->get()->toArray();

        $this->setAttribute('tree', $tree);
        $this->setAttribute('moduleName', $module->getName());
    }
}

