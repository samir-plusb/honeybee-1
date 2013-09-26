<?php

namespace Honeybee\Agavi\View;

class ImportSuccessView extends BaseView
{
    public function executeConsole(\AgaviRequestDataHolder $request_data)
    {
        return "Successfully finished import." . PHP_EOL;
    }
}
