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
        $treeRelationData = $parameters->getParameter('tree_relation_data');

        foreach($treeRelationData as $treeRelation)
        {
            $this->getLayer('content')->setSlot(
                $treeRelation['treeModule'],
                $this->createSlotContainer('Common', 'SidebarTree', array(
                    'treeModule' => $treeRelation['treeModule'],
                    'localModule' => $treeRelation['localModule'],
                    'referenceField' => $treeRelation['referenceField']
                ), NULL, 'read')
            );
        }
    }
}
