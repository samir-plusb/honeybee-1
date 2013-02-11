<?php

namespace Honeybee\Agavi\Action;

use Honeybee\Core\Dat0r\Tree;
use TreeConfig;

class TreeAction extends BaseAction
{
    public function executeRead(\AgaviRequestDataHolder $requestData)
    {   
        $module = $this->getModule();
        $tree = $module->getService('tree')->get();

        $this->setAttribute('module', $module);
        $this->setAttribute('tree', $tree);
        $this->setAttribute('config', TreeConfig::create($this->buildTreeConfig()));

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

    protected function buildTreeConfig()
    {
        $settingsKey = $this->buildTreeConfigKey();
        $treeSettings = \AgaviConfig::get($settingsKey, array());
        $fields = array_values($this->getModule()->getFields()->toArray());

        if (! isset($treeSettings['fields']))
        {
            $listFields = array();

            for($i = 0; $i < 5 && $i < count($fields); $i++)
            {
                $field = $fields[$i];
                $listFields[$field->getName()] = array(
                    'name' => $field->getName(),
                    'valuefield' => $field->getName(),
                    'sortfield' => sprintf('%s.raw', $field->getName())
                );
            }
            $treeSettings['fields'] = $listFields;
        }

        return $treeSettings;
    }

    protected function buildTreeConfigKey()
    {
        return sprintf(
            '%s.tree_config', 
            $this->getModule()->getOption('prefix')
        );
    }
}

