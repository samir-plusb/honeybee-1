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
        $treeConfig = $parameters->getParameter('config');
        $this->setAttribute('batch_actions',$treeConfig->getBatchActions());

        /*
         * Here we call the Listaction of the current module
         * to get the complete document data so we can render the correct wirkflow buttons in our GUI
         */
        $rd = new AgaviRequestDataHolder(array(
            'limit' => 10000,
            'offset' => 0,
        ));
        $container = $this->getContext()->getController()->createExecutionContainer(
            $this->getModule()->getName(),
            'List',
            $rd,
            'json',
            'read'
        );

        $listData = json_decode($container->execute()->getContent(), TRUE);
        $documents = array();
        foreach($listData as $listItem)
        {
            $documents[$listItem['data']['identifier']] = $listItem;
        }

        $this->setAttribute('documents', $documents);
        $this->setAttribute('translation_domain', $treeConfig->getTranslationDomain());

        return 'Success';
    }
}
