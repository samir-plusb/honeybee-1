<?php

namespace Honeybee\Agavi\View;

class ImportSuccessView extends BaseView
{
    public function executeConsole(\AgaviRequestDataHolder $request_data)
    {
        $this->getResponse()->setContent("Successfully finished import." . PHP_EOL);
        $this->getResponse()->setExitCode(0);
    }
}
