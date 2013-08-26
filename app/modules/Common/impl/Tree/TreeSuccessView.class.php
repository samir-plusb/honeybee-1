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
        $this->setAttribute('select_only_mode', $parameters->hasParameter('referenceField'));

        $listParams = array();
        if ($parameters->hasParameter('referenceField'))
        {
            $listParams = array(
                'referenceField' => $parameters->getParameter('referenceField'),
                'referenceModule' => $parameters->getParameter('referenceModule')
            );
        }

        $routing = $this->getContext()->getRouting();

        $this->setAttribute('list_view_link', $routing->gen($treeConfig->getTypeKey() . '.list', $listParams));
        $this->setAttribute('tree_view_link', $routing->gen($treeConfig->getTypeKey() . '.tree'));

        $clientSideOptions = $treeConfig->getClientSideController();
        $clientSideOptions['options'] = isset($clientSideOptions['options']) ? $clientSideOptions['options'] : array();
        $clientSideOptions['options']['workflow_urls'] = array(
            'checkout' => urldecode(htmlspecialchars_decode(
                $routing->gen(sprintf('%s.workflow.checkout', $treeConfig->getTypeKey()))
            )),
            'release' => urldecode(htmlspecialchars_decode(
                $routing->gen(sprintf('%s.workflow.release', $treeConfig->getTypeKey()))
            )),
            'execute' => urldecode(htmlspecialchars_decode(
                $routing->gen(sprintf('%s.workflow.execute', $treeConfig->getTypeKey()))
            )),
            'edit' => urldecode(htmlspecialchars_decode(
                $routing->gen(sprintf('%s.edit', $treeConfig->getTypeKey()))
            ))
        );
        $clientSideOptions['options']['select_only_mode'] = $this->getAttribute('select_only_mode', true);

        $this->setAttribute('client_side_controller', $clientSideOptions);
    }
}

