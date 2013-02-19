<?php

/**
 * The Common_Tree_TreeErrorView class handles Common/Tree error data presentation.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Leon Weidauer <leon.weidauer@gmail.com>
 * @package         Common
 * @subpackage      Mvc
 */
class Common_Tree_TreeErrorView extends CommonBaseView
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
    }
}

