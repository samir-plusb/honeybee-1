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

        $treeModuleClass = $parameters->getParameter('treeModule');
        $localModuleClass = $parameters->getParameter('localModule');
        $referenceField = $parameters->getParameter('referenceField');

        $treeModule = $treeModuleClass::getInstance();
        $localModule = $localModuleClass::getInstance();
        $tree = $treeModule->getService('tree')->get()->toArray();

        $this->setAttribute('tree', $tree);
        $this->setAttribute('treeModule', $treeModule->getName());
        $this->setAttribute('localModule', $localModule->getName());
        $this->setAttribute('fieldName', $localModule->getField($referenceField)->getName());
        $this->setAttribute('tree_relation', array(
            'treeModule' => $treeModule->getOption('prefix'),
            'localModule' => $localModule->getOption('prefix'),
            'referenceField' => $referenceField
        ));
    }
}

