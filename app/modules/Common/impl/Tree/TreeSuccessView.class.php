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
class Common_Tree_TreeSuccessView extends CommonBaseView
{
    public function executeHtml(\AgaviRequestDataHolder $parameters)
    {
        $this->setupHtml($parameters);
        $tree = $parameters->getParameter('tree');
        $treeConfig = $parameters->getParameter('config');

        $this->setAttribute('module_type_key', $treeConfig->getTypeKey());
        $this->setAttribute('tree', $tree->toArray());
        $this->setAttribute('client_side_controller', $treeConfig->getClientSideController());
    }
}

