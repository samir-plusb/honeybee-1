<?php

namespace Honeybee\Agavi\View;

class TreeSuccessView extends BaseView
{
    public function executeHtml(\AgaviRequestDataHolder $parameters)
    {
        $this->setupHtml($parameters);

        $module = $this->getAttribute('module');
        $this->setAttribute('_title', sprintf('Honeybee - %s: Hierarchie', $module->getName()));

        $this->getLayer('content')->setSlot(
            'tree',
            $this->createSlotContainer('Common', 'Tree', array(
                'tree' => $this->getAttribute('tree'),
                'config' => $this->getAttribute('config'),
            ), NULL, 'read')
        );
    }

    public function executeJson(\AgaviRequestDataHolder $requestData)
    {
        $this->getResponse()->setContent(json_encode(
            $this->getAttribute('tree')->toArray()
        ));
    }
}
