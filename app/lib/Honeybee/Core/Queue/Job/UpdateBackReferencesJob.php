<?php

namespace Honeybee\Core\Queue\Job;

use Honeybee\Core\Dat0r\Document;
use Honeybee\Core\Dat0r\ModuleService;
use Dat0r\Core\Document\DocumentSet;

class UpdateBackReferencesJob extends DocumentJob
{
    protected function execute(array $parameters = array())
    {
        $document = $this->loadDocument();

        foreach ($document->getModule()->getReferencingFieldIndices() as $reference_meta_data) {
            $referencing_module = $reference_meta_data['reference_module'];
            $referencing_field = $reference_meta_data['reference_field'];
            $index_fields = $reference_meta_data['index_fields'];

            $reference_id_fieldname = $referencing_field->getName() . '.id';
            $search_spec = array(
                'filter' => array($reference_id_fieldname => $document->getIdentifier())
            );

            $service = $referencing_module->getService();
            $search_result = $service->find($search_spec, 0, 1000);
            foreach ($search_result['documents'] as $referencing_document) {
                if ($document->getIdentifier() === $referencing_document->getIdentifier()) {
                    // prevent recursion for self references,
                    // I'm not sure if we should support the 'index_fields' feature in this case.
                    continue;
                }
                $document_set = new DocumentSet();
                foreach ($referencing_document->getValue($referencing_field->getName()) as $ref_document) {
                    if ($ref_document->getIdentifier() === $document->getIdentifier()) {
                        // override any old referenced document with the current one ...
                        $document_set->add($document);
                    } else {
                        // ... keep all others.
                        $document_set->add($ref_document);
                    }
                }
                $referencing_document->setValue($referencing_field->getName(), $document_set);
                $service->save($referencing_document);
            }
        }
    }
}
