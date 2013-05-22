<?php

use Dat0r\Core\Runtime\Document\IDocument;
use Dat0r\Core\Runtime\Module\AggregateModule;

class AggregateListRenderer extends DefaultListValueRenderer
{
    public function renderValue($value, $field, array $data = array())
    {
        if ($value instanceof IDocument)
        {
            $module = $value->getModule();

            if ($module instanceof AggregateModule)
            {
                $rendererDef = $field->getRenderer();
                $displayField = isset($rendererDef['options']['displayField']) ? $rendererDef['options']['displayField'] : 'identifier';
                $value = $value->getValue($displayField);
            }
            else
            {
                throw new Exception("Only aggregate modules supported by the AggregateListRenderer.");
            }
        }
        else
        {
            throw new Exception("Only aggregate documents may be rendered by the AggregateListRenderer.");
        }

        $translate = isset($rendererDef['options']['translate']) ? (bool)$rendererDef['options']['translate'] : FALSE;
        if ($translate)
        {
            $defaultDomain = sprintf('%s.list', $this->module->getOption('prefix'));
            $domain = isset($rendererDef['options']['domain']) ? $rendererDef['options']['domain'] : $defaultDomain;
            $tm = AgaviContext::getInstance()->getTranslationManager();
            $value = $tm->_($value, $domain);
        } 

        return $value;
    }
}
