<?php

namespace Honeybee\Core\Queue\Job;

use Honeybee\Core\Dat0r\Document;
use Honeybee\Core\Dat0r\ModuleService;

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
                'filter' => array(
                    $reference_id_fieldname => $document->getIdentifier()
                )
            );

            $service = $referencing_module->getService();
            $search_result = $service->find($search_spec, 0, 1000);
            foreach ($search_result['documents'] as $referencing_document) {
                if ($document->getIdentifier() === $referencing_document->getIdentifier()) {
                    // prevent recursion for self references,
                    // I'm not sure if we should support the 'index_fields' feature in this case.
                    continue;
                }

                $service->save($referencing_document);
                error_log(sprintf("[%s] Updated %s", __CLASS__, $referencing_document->getIdentifier()));
            }
        }
        sleep(10);
    }
}
