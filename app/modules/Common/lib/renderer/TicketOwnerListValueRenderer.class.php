<?php

use Dat0r\Core\Document\DocumentCollection;

class TicketOwnerListValueRenderer extends DefaultListValueRenderer
{
    public function renderValue($value, $field, array $data = array())
    {
        if ($value instanceof DocumentCollection) {
            $value = $value->first()->getOwner();
        }

        return parent::renderValue($value, $field, $data);
    }

    protected function getTemplateDirectory()
    {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR;
    }

    protected function getTemplateFilename()
    {
        return 'WorkflowState.tpl.twig';

    }
}

?>
