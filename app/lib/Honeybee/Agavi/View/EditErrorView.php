<?php

namespace Honeybee\Agavi\View;

class EditErrorView extends BaseView
{
    public function executeJson(\AgaviRequestDataHolder $parameters)
    {
        $data = array(
            'state' => 'error',
            'messages' => array(),
            'errors' => $this->getAttribute('errors', array()),
            'data' => array()
        );

        $this->getResponse()->setContent(json_encode($data));
    }
}
