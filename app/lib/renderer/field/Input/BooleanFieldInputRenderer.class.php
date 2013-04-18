<?php

use Honeybee\Core\Dat0r\Document;

class BooleanFieldInputRenderer extends FieldInputRenderer
{
    protected function renderDisplayValue(Document $document)
    {
        return (bool)$document->getValue($this->getField()->getName());
    }

    protected function getTemplateName()
    {
        return "Checkbox.tpl.twig";
    }
}
