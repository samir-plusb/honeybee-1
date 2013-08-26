<?php

namespace Honeybee\Agavi\Action;

use Honeybee\Core\Dat0r\Tree;
use Dat0r\Core\Field\ReferenceField;
use TreeConfig;

class TreeAction extends BaseAction
{
    public function executeRead(\AgaviRequestDataHolder $requestData)
    {
        $module = $this->getModule();

        if ($requestData->hasParameter('referenceField') && $requestData->hasParameter('referenceModule'))
        {
            $fieldname = $requestData->getParameter('referenceField');
            $moduleClass = sprintf('Honeybee\\Domain\\%1$s\\%1$sModule', $requestData->getParameter('referenceModule'));
            $referenceModule = $moduleClass::getInstance();
            $referenceField = $referenceModule->getField($fieldname);

            $this->setAttribute('referenceModule', $referenceModule);
            $this->setAttribute('referenceField', $referenceField);
        }

        $tree = $module->getService('tree')->get('tree-default');

        $this->setAttribute('module', $module);
        $this->setAttribute('tree', $tree);
        $this->setAttribute('config', TreeConfig::create($this->buildTreeConfig()));

        return 'Success';
    }

    public function executeWrite(\AgaviRequestDataHolder $requestData)
    {
        $module = $this->getModule();
        $treeService = $module->getService('tree');
        $previousTree = $treeService->get();

        $tree = new Tree\Tree(
            $module,
            $requestData->getParameter('structure')
        );

        $movedNodes = array();

        $iterator = $tree->getIterator();
        $iterator->next();

        foreach ($previousTree->getIterator() as $node)
        {
            if (!$iterator->valid())
            {
                break;
            }

            $curDepth = $node->getDepth();
            $lastDepth = $iterator->current()->getDepth();
            $curIdentifier = $node->getIdentifier();
            $lastIdentifier = $iterator->current()->getIdentifier();

            if ($curDepth !== $lastDepth || $curIdentifier !== $lastIdentifier)
            {
                $movedNodes[] = $iterator->current();
            }

            $iterator->next();
        }

        $treeService->save($tree);

        foreach ($movedNodes as $movedNode)
        {
            $movedNode->getDocument()->onTreePositionChanged();
        }

        $this->setAttribute('module', $module);
        $this->setAttribute('tree', $tree);

        return 'Success';
    }

    protected function buildTreeConfig()
    {
        $routing = $this->getContext()->getRouting();
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

        $treeSettings['clientSideController']['options']['module'] = $this->getModule()->getOption('prefix');
        $treeSettings['clientSideController']['options']['event_origin'] = $routing->getBaseHref();

        if ($this->hasAttribute('referenceField'))
        {
            $referenceField = $this->getAttribute('referenceField');
            $referenceModule = $this->getAttribute('referenceModule');

            foreach ($referenceField->getOption(ReferenceField::OPT_REFERENCES) as $reference)
            {
                if ('\\' . get_class($this->getModule()) === $reference[ReferenceField::OPT_MODULE])
                {
                    $treeSettings['clientSideController']['options']['reference_field'] = $referenceField->getName();
                    $treeSettings['clientSideController']['options']['select_only_mode'] = TRUE;
                    $treeSettings['clientSideController']['options']['reference_module'] = $referenceModule->getName();
                    $treeSettings['clientSideController']['options']['reference_settings'] = array(
                        'identity_field' => $reference[ReferenceField::OPT_IDENTITY_FIELD],
                        'display_field' => $reference[ReferenceField::OPT_DISPLAY_FIELD]
                    );
                    break;
                }
            }
        }

        return $treeSettings;
    }

    public function getCredentials()
    {
        return sprintf(
            '%s::%s',
            $this->getModule()->getOption('prefix'), 
            $this->getContainer()->getRequestMethod()
        );
    }

    protected function buildTreeConfigKey()
    {
        return sprintf(
            '%s.tree_config', 
            $this->getModule()->getOption('prefix')
        );
    }
}

