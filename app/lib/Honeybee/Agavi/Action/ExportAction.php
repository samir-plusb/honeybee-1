<?php

namespace Honeybee\Agavi\Action;

class ExportAction extends BaseAction
{
    public function executeWrite(\AgaviRequestDataHolder $parameters)
    {
        $export_name = $parameters->getParameter('provider', 'pulq-fe');
        $chunk_size = $parameters->getParameter('chunk_size', 100);

        $module = $this->getModule();
        $export_service = $module->getService('export');
        $document_service = $module->getService();

        $search_spec = array('filter' => array('workflowTicket.workflowStep' => 'published'));
        $publish_document = function($document) use ($export_service, $export_name)
        {
            $export_service->publish($export_name, $document);
        };
        $document_service->walkDocuments($search_spec, $chunk_size, $publish_document);

        return 'Success';
    }

    public function isSecure()
    {
        return FALSE;
    }
}
