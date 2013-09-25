<?php

namespace Honeybee\Agavi\Action;

class ExportAction extends BaseAction
{
    public function executeWrite(\AgaviRequestDataHolder $parameters)
    {
/*
        $user = $this->getContext()->getUser();
        $user->setAuthenticated(true);
        $user->setAttribute('acl_role', 'honeybee-editor');
        $export_name = $parameters->getParameter('provider', 'pulq-fe');
        $chunk_size = $parameters->getParameter('chunk_size', 10000);

        $module = $this->getModule();
        $export_service = $module->getService('export');
        $document_service = $module->getService();

        $manager = $module->getWorkflowManager();
        $container = $this->getContainer();
$proc_docs = 0;
        $search_spec = array('filter' => array('workflowTicket.workflowStep' => 'edit'));
        $publish_document = function($document) use ($manager, $proc_docs, $document_service)
        {
            $proc_docs++;
            //$manager->executeWorkflowFor($document, 'demote', $container);
            //$document_service->save($document);
            //$export_service->publish($export_name, $document);
        };
        $document_service->walkDocuments($search_spec, $chunk_size, $publish_document);
var_dump($proc_docs);
    */

        $user = $this->getContext()->getUser();
        $user->setAuthenticated(true);
        $user->setAttribute('acl_role', 'honeybee-editor');

        $module = $this->getModule();
        $manager = $module->getWorkflowManager();
        $doc_service = $module->getService();

        $offset = 0;
        $limit = 1000;
        $data = $doc_service->fetchAll($offset, $limit);
        $total_docs = $data['totalCount'];
        $cur_doc_count = count($data['documents']);
        $container = $this->getContainer();
        while(0 < $cur_doc_count)
        {
            $collection = $data['documents'];

            foreach ($collection as $document)
            {
                $workflow_step = $document->getWorkflowTicket()->first()->getWorkflowStep();
                if ('edit' === $workflow_step)
                {
                    //$document->getWorkflowTicket()->first()->setWorkflowStep('edit');
                    $manager->executeWorkflowFor($document, 'promote', $container);
                    $doc_service->save($document);
                }
            }

            $offset += $limit;
            $data = $doc_service->fetchAll($offset, $limit);
            $cur_doc_count = count($data['documents']);
        }

        return 'Success';
    }

    public function isSecure()
    {
        return FALSE;
    }
}
