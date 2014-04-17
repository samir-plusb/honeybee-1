<?php

use Dat0r\Core\Document\DocumentCollection;

class ReferenceListValueRenderer extends DefaultListValueRenderer
{
    public function renderValue($value, $field, array &$data = array())
    {
        $rendererDef = $field->getRenderer();
        $options = $rendererDef['options'];
        if ($value instanceof DocumentCollection && 0 < count($value))
        {
            $displayField = isset($options['displayField']) ? $options['displayField'] : 'identifier';
            $value = $value->first()->getValue($displayField);

            if (empty($value))
            {
                $value = isset($options['default']) ? $options['default'] : '';
            }
        }
        else
        {
            $value = isset($rendererDef['options']['default']) ? $rendererDef['options']['default'] : '';
        }

        if (isset($options['translate']) && true === $options['translate'])
        {
            $translation_domain = sprintf('%s.list', $this->module->getOption('prefix'));
            $translation_domain = isset($options['domain']) ? $options['domain'] : $translation_domain;
            $value = AgaviContext::getInstance()->getTranslationManager()->_($value, $translation_domain);
        }

        return $value;
    }
}
