<?php

use Dat0r\Core\Document\IDocument;

class BooleanFieldInputRenderer extends FieldInputRenderer
{
    protected function renderDisplayValue(IDocument $document)
    {
        return (bool)$document->getValue($this->getField()->getName());
    }

    protected function getTemplateName()
    {
        return "Checkbox.tpl.twig";
    }
}
