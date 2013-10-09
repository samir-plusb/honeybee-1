<?php

use Dat0r\Core\Document\IDocument;

class KoReadOnlyFieldInputRenderer extends FieldInputRenderer
{
    protected function getPayload(IDocument $document)
    {
        $payload = parent::getPayload($document);

        $payload['bindInput'] = isset($this->options['bind_value'])
            ? (bool)$this->options['bind_value']
            : true;

        return $payload;
    }

    protected function getTemplateName()
    {
        return 'KoReadOnlyField.tpl.twig';
    }
}
