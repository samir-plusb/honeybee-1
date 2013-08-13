<?php

use Dat0r\Core\Document\IDocument;
use Dat0r\Core\Document\DocumentCollection;
use Dat0r\Core\Module\AggregateModule;

class AggregateListRenderer extends DefaultListValueRenderer
{
    public function renderValue($value, $field, array $data = array())
    {
        if ($value instanceof DocumentCollection)
        {
            $displayTokens = array();
            foreach ($value as $aggregate_document)
            {
                $module = $aggregate_document->getModule();
                if ($module instanceof AggregateModule)
                {
                    $rendererDef = $field->getRenderer();
                    $displayField = isset($rendererDef['options']['displayField'])
                        ? $rendererDef['options']['displayField']
                        : 'identifier';

                    $displayTokens[] = $aggregate_document->getValue($displayField);
                }
                else
                {
                    throw new Exception("Only aggregate modules supported by the AggregateListRenderer.");
                }
            }
            $value = implode(', ', $displayTokens);
        }
        else
        {
            throw new Exception("Only aggregate DocumentCollections may be rendered by the AggregateListRenderer.");
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
