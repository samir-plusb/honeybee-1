<?php

namespace Honeybee\Agavi\Action;

use Honeybee\Core\Dat0r\Tree;

class TreeAction extends BaseAction
{
    public function executeRead(\AgaviRequestDataHolder $requestData)
    {   
        $module = $this->getModule();
        $tree = $module->getService('tree')->get();

        $this->setAttribute('module', $module);
        $this->setAttribute('tree', $tree);

        return 'Success';
    }

    public function executeWrite(\AgaviRequestDataHolder $requestData)
    {
        $module = $this->getModule();

        $tree = new Tree\Tree(
            $module, 
            $requestData->getParameter('structure')
        );

        $module->getService('tree')->save($tree);

        $this->setAttribute('module', $module);
        $this->setAttribute('tree', $tree);

        return 'Success';
    }
}
