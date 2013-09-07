<?php

use Dat0r\Core\Document\IDocument;

class TextareaFieldInputRenderer extends FieldInputRenderer
{
    protected function getPayload(IDocument $document)
    {
        $payload = parent::getPayload($document);
        $render_label = false;
        if (isset($this->options['render_label']) && true === $this->options['render_label']) {
            $render_label = true;
        }
        $payload['render_label'] = $render_label;

        return $payload;
    }

    protected function getTemplateName()
    {
        return "Textarea.tpl.twig";
    }
}
