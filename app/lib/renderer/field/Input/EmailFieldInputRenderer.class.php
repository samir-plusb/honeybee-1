<?php

use Dat0r\Core\Document\IDocument;

class EmailFieldInputRenderer extends FieldInputRenderer
{
    protected function getTemplateName()
    {
        return "Email.tpl.twig";
    }

    protected function getPayload(IDocument $document)
    {
        $payload = parent::getPayload($document);

        if (!isset($payload['placeholder'])) {
            $payload['placeholder'] = 'max.mustermann@example.com';
        }

        return $payload;
    }
}
