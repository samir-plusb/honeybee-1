<?php

namespace Honeybee\Agavi\View;

class TreeSuccessView extends BaseView
{
    public function executeHtml(\AgaviRequestDataHolder $requestData)
    {
        parent::setupHtml($requestData);

        print_r($this->getAttribute('tree')->toArray());exit;
    }

    public function executeJson(\AgaviRequestDataHolder $requestData)
    {
        $this->getResponse()->setContent(json_encode(
            $this->getAttribute('tree')->toArray()
        ));
    }
}
