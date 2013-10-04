<?php

use Dat0r\Core\Document\IDocument;

class SelectFieldInputRenderer extends FieldInputRenderer
{
    protected function getPayload(IDocument $document)
    {
        $payload = parent::getPayload($document);
        $payload['select_options'] = $this->getSelectionOptions($document);
        $payload['multiple'] = $this->getField()->getOption('multiple', false);

        return $payload;
    }

    protected function getWidgetOptions(IDocument $document)
    {
        $parentOptions = parent::getWidgetOptions($document);

        $fieldname = $this->getField()->getName();
        $selected_options = $document->getValue($fieldname);
        $selected_options = is_array($selected_options) ? $selected_options : array();
        $translation_manager = $this->getTranslationManager();
        $domain = $this->getTranslationDomain($document) . '.' . $this->getField()->getName();

        $tags = array();
        foreach ($selected_options as $option) {
            $label = $translation_manager->_($option, $domain);
            $tags[] = array('label' => $label, 'value' => $option);
        }

        $autocomplete_values = array();
        foreach ($this->getSelectionOptions($document) as $option => $label) {
            $autocomplete_values[] = array('label' => $label, 'value' => $option);
        }

        return array_merge($parentOptions, array(
            'fieldname' => $this->generateInputName($document),
            'max' => 0,
            'tags' => $tags,
            'tpl' => 'Stack',
            'autocomplete' => true,
            'autocomplete_display_prop' => 'label',
            'autocomplete_value_prop' => 'value',
            'autocomplete_values' => $autocomplete_values
        ));
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
        if (!isset($this->options['sort_values']) || true == $this->options['sort_values']) {
            ksort($select_values);
        }

        return $select_values;
    }

    protected function getTemplateName()
    {
        if ($this->getField()->getOption('multiple', false)) {
            return "MultiSelect.tpl.twig";
        } else {
            return "Select.tpl.twig";
        }
    }
}
