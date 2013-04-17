<?php

namespace Honeybee\Agavi\View;

class ImportSuccessView extends BaseView
{
    public function executeText(\AgaviRequestDataHolder $parameters) 
    {
        $this->getResponse()->setContent("Successfully finished import.");
    }
}
