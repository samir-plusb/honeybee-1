<?php

namespace Honeybee\Agavi\Action;

use Honeybee\Core\Import\Service;

class ImportAction extends BaseAction
{
    public function executeWrite(\AgaviRequestDataHolder $request_data)
    {
        $consumer_name = $request_data->getParameter('consumer');

        $service = $this->getModule()->getService('import');

        try
        {
            $report = $service->consume($consumer_name);
        }
        catch (\Exception $e)
        {
            $this->logError($e);
            //$this->logTrace('Details aus Validierung:', $this->getContainer()->getValidationManager(), $e, PHP_EOL . "\nwoohooo\n\n");
            //$this->logDebug($this->getModule(), 'ist ungültig');
            //$this->getContext()->getLoggerManager()->getLogger('default')->getPsr3Logger()->log(\Psr\Log\LogLevel::CRITICAL, 'Everybody get down, this {beep}', array('beep' => 'is a robbery!!!11'));
            return 'Error';
        }

        return 'Success';
    }

    public function isSecure()
    {
        return false;
    }
}
