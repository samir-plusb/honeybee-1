<?php

use Dat0r\Core\Runtime\Document\DocumentCollection;

class ReferenceListValueRenderer extends DefaultListValueRenderer
{
    public function renderValue($value, $field, array $data = array())
    {
        $rendererDef = $field->getRenderer();

        if ($value instanceof DocumentCollection && 0 < count($value))
        {
            $displayField = isset($rendererDef['options']['displayField']) ? $rendererDef['options']['displayField'] : 'identifier';
            $value = $value->first()->getValue($displayField);

            if (empty($value))
            {
                $value = isset($rendererDef['options']['default']) ? $rendererDef['options']['default'] : '';
            }
        }
        else
        {
            $value = isset($rendererDef['options']['default']) ? $rendererDef['options']['default'] : '';
        }

        return $value;
    }
}
