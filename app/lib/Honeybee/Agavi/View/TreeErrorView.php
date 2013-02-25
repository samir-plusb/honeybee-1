<?php

namespace Honeybee\Agavi\View;

class TreeErrorView extends BaseView
{
    public function executeHtml(\AgaviRequestDataHolder $requestData)
    {
        parent::setupHtml($requestData);
        // do we need/support html here?
        return '<h1>I can haz tree-error html?</h1>';
    }

    public function executeJson(\AgaviRequestDataHolder $parameters)
    {
        $data = array(
            'state' => 'error',
            'errors' => $this->getAttribute('errors', array())
        );
        
        $this->getResponse()->setContent(json_encode($data));
    }
}
