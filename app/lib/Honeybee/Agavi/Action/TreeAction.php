<?php

namespace Honeybee\Agavi\Action;

use Honeybee\Core\Dat0r\Tree;

class TreeAction extends BaseAction
{
    public function executeRead(\AgaviRequestDataHolder $requestData)
    {   
        $module = $this->getModule();
        $tree = $module->getService()->getTree();

        $this->setAttribute('module', $module);
        $this->setAttribute('tree', $tree);

        return 'Success';
    }

    public function executeWrite(\AgaviRequestDataHolder $requestData)
    {
        $treeData = $requestData->getParameter('structure');
        // to see the expected structure: $this->getModule()->getService()->getTree()->toArray()
        $tree = new Tree\Tree($this->getModule(), $treeData['name']);
        $tree->hydrate($treeData);

        $this->getModule()->getService()->storeTree($tree);

        $this->setAttribute('tree', $tree);

        return 'Success';
    }
}

