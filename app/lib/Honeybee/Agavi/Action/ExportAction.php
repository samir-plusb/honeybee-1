<?php

namespace Honeybee\Agavi\Action;

class ExportAction extends BaseAction
{
    public function executeWrite(\AgaviRequestDataHolder $parameters)
    {
        $module = $this->getModule();
        $exportService = $module->getService('export');
        $docService = $module->getService();

        $offset = 0;
        $limit = 100;
        $data = $docService->fetchAll($offset, $limit);
        $curDocCount = count($data['documents']);
        $docCollection = array();
        
        while(0 < $curDocCount)
        {
            $totalDocs = $data['totalCount'];
            $docCollection = $data['documents'];

            foreach ($docCollection as $document)
            {
                foreach ($exportService->getExports() as $export)
                {
                    $export->export($document);
                }
            }

            $offset += $limit;
            $data = $docService->fetchAll($offset, $limit);
            $curDocCount = count($data['documents']);
        }
        
        return 'Success';
    }

    public function isSecure()
    {
        return FALSE;
    }
}
