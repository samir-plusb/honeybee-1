<?php

use Dat0r\Core\Document\IDocument;

class SelectFieldInputRenderer extends FieldInputRenderer
{
    protected function getPayload(IDocument $document)
    {
        $payload = parent::getPayload($document);
        $payload['select_options'] = $this->getSelectionOptions($document);

        return $payload;
    }

    protected function getSelectionOptions(IDocument $document)
    {
        $select_values = array();
        $translation_manager = $this->getTranslationManager();
        $domain = $this->getTranslationDomain($document) . '.' . $this->getField()->getName();
        foreach ($this->getField()->getOption('options', array()) as $option) {
            $label = $translation_manager->_($option, $domain);
            $select_values[$option] = $label;
        }
        if (!isset($this->options['sort_values']) || true == $this->options['sort_Values']) {
            ksort($select_values);
        }

        return $select_values;
    }

    protected function getTemplateName()
    {
        return "Select.tpl.twig";
    }
}
