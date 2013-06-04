<?php

namespace Honeybee\Agavi\Action;

use Honeybee\Core\Import\Service;

class ImportAction extends BaseAction
{
    public function executeWrite(\AgaviRequestDataHolder $request_data)
    {
        $consumer_name = $request_data->getParameter('consumer');

        $service = $this->getModule()->getService('import');

        $this->logDebug('Trying to import entries into', $this->getModule(), "for the specified consumer '$consumer_name'.");

        try
        {
            $report = $service->consume($consumer_name);
        }
        catch (\Exception $e)
        {
            $this->logError(
                'Import for {module} and consumer {consumer} failed. Exception: {cause}',
                array(
                    'module' => $this->getModule(),
                    'consumer' => $consumer_name,
                    'cause' => $e,
                    //'scope' => 'Import'
                )
            );

            return 'Error';
        }

        return 'Success';
    }

    public function isSecure()
    {
        return false;
    }
}
