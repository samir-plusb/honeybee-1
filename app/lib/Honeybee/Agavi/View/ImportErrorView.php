<?php

namespace Honeybee\Agavi\View;

class ImportErrorView extends BaseView
{
    public function executeText(\AgaviRequestDataHolder $parameters) 
    {
        $this->getResponse()->setContent("Error while trying to import." . PHP_EOL);
        $this->getResponse()->setExitCode(1);
    }
}
