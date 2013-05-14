<?php

namespace Honeybee\Agavi\Action;

use Honeybee\Core\Import\Service;

class ImportAction extends BaseAction
{
    public function executeWrite(\AgaviRequestDataHolder $parameters)
    {
        $consumerName = $parameters->getParameter('consumer');

        $service = $this->getModule()->getService('import');

        $report = $service->consume($consumerName);

        return 'Success';
    }

    public function isSecure()
    {
        return FALSE;
    }
}
