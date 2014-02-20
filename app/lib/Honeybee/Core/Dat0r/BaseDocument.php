<?php

namespace Honeybee\Core\Dat0r;

use Dat0r\Core\Document\Document;
use Dat0r\Core\Field\ReferenceField;
use Zend\Permissions\Acl\Resource\ResourceInterface;

abstract class BaseDocument extends Document implements ResourceInterface
{
    public function getResourceId()
    {
        return strtolower($this->getModule()->getName());
    }

    protected function hydrate(array $values = array(), $apply_defaults = FALSE)
    {
        $reference_data = array();
        if (! empty($values)) {
            foreach ($this->getModule()->getFields() as $fieldname => $field) {
                if (($field instanceof ReferenceField) && isset($values[$fieldname])) {
                    $reference_data[$fieldname] = $values[$fieldname];
                    unset($values[$fieldname]);
                }
            }
        }

        parent::hydrate($values, $apply_defaults);

        $max_depth = RelationManager::getMaxRecursionDepth();
        if (-1 === $max_depth || RelationManager::getRecursionDepth() <= $max_depth) {
            foreach (RelationManager::loadReferences($this, $reference_data) as $fieldname => $reference_documents) {
                $this->setValue($fieldname, $reference_documents);
            }
        }
    }
}
