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

        $filter = array('workflowTicket.workflowStep' => 'published');
        $searchSpec = array('filter' => $filter);

        $data = $docService->find($searchSpec, $offset, $limit);
        $totalDocs = $data['totalCount'];
        $curDocCount = count($data['documents']);

        while(0 < $curDocCount)
        {
            $docCollection = $data['documents'];

            foreach ($docCollection as $document)
            {
                $exportService->export('pulq-fe', $document);
            }

            $offset += $limit;
            $data = $docService->find($searchSpec, $offset, $limit);
            $curDocCount = count($data['documents']);
        }
        
        return 'Success';
    }

    public function isSecure()
    {
        return FALSE;
    }
}
