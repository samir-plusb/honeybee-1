<?php

namespace Honeybee\Agavi\Action;

use Honeybee\Core\Config\ArrayConfig;
use AgaviRequestDataHolder;
use AgaviConfig;

class ExportAction extends BaseAction
{
    public function executeWrite(AgaviRequestDataHolder $parameters)
    {
        $user = $this->getContext()->getUser();
        $user->setAuthenticated(true);
        $user->setAttribute('acl_role', 'honeybee-editor');

        $export_name = $parameters->getParameter('provider', 'pulq-fe');
        $chunk_size = $parameters->getParameter('chunk_size', 1000);

        $module = $this->getModule();
        $export_service = $module->getService('export');
        $document_service = $module->getService();

        $module_prefix = $this->getModule()->getOption('prefix');
        $publish_steps = AgaviConfig::get(
            $module_prefix . '.workflow.publish_steps',
            AgaviConfig::get('workflow.publish_steps', array('published'))
        );

        // modules with different publish steps can define custom publish step via parameter
        if ($parameters->getParameter('publish_step'))
        {
            $publish_steps[0] = $parameters->getParameter('publish_step');
        }

        $exported_doc_count = 0;
        $search_spec = array('filter' => array('workflowTicket.workflowStep' => $publish_steps));
        $publish_document = function($document) use ($export_name, $export_service, &$exported_doc_count)
        {
            $export_service->publish($export_name, $document);
            $exported_doc_count++;
        };
        $document_service->walkDocuments($search_spec, $chunk_size, $publish_document, true);

        $this->setAttribute('exported_doc_count', $exported_doc_count);

        return 'Success';
    }

    public function isSecure()
    {
        return FALSE;
    }
}
