<?php

use Dat0r\Core\Runtime\Field\ReferenceField;
use Honeybee\Core\Dat0r\Document;

class ReferenceFieldInputRenderer extends FieldInputRenderer
{
    protected function getWidgetType(Document $document)
    {
        return 'widget-tags-list';
    }

    protected function getWidgetOptions(Document $document)
    {
        $references = $this->getField()->getOption(ReferenceField::OPT_REFERENCES);

        if (1 < count($references))
        {
            throw new Exception("Atm multiple references are not supported.");
        }

        $reference = $references[0];
        $moduleImplementor = $reference[ReferenceField::OPT_MODULE];
        $displayField = $reference[ReferenceField::OPT_DISPLAY_FIELD];
        $identityField = $reference[ReferenceField::OPT_IDENTITY_FIELD];

        $referencedModule = $moduleImplementor::getInstance();
        $tags = array();

        if ($documents = $document->getValue($this->getField()->getName()))
        {
            foreach ($documents as $refDocument)
            {
                $tags[] = array(
                    'label' => $refDocument->getValue($displayField),
                    'value' => $refDocument->getValue($identityField)
                );
            }
        }

        return array_merge(
            $this->buildAutoCompleteOptions($referencedModule),
            array(
                'autobind' => TRUE,
                'fieldname' => $this->generateInputName($document),
                'max' => $this->getField()->getOption(ReferenceField::OPT_MAX_REFERENCES, 0),
                'tags' => $tags
            )
        );
    }

    protected function buildAutoCompleteOptions()
    {
        $references = $this->getField()->getOption(
            ReferenceField::OPT_REFERENCES
        );
        if (1 < count($references))
        {
            throw new Exception("Atm multiple references are not supported.");
        }

        $reference = $references[0];
        $referenceModuleClass = $reference['module'];
        $displayField = $reference[ReferenceField::OPT_DISPLAY_FIELD];
        $identityField = $reference[ReferenceField::OPT_IDENTITY_FIELD];
        $referencedModule = $referenceModuleClass::getInstance();
        $modulePrefix = $referencedModule->getOption('prefix');
        $suggestRouteName = sprintf('%s.suggest', $modulePrefix);
        $suggestLink = htmlspecialchars_decode(urldecode($this->getRouteLink($suggestRouteName, array(
            'term' => '{PHRASE}',
            'display_field' => $displayField,
            'identity_field' => $identityField
        ))));

        return array(
            'autocomplete' => TRUE,
            'autocomplete_uri' => $suggestLink,
            'autocomplete_display_prop' => $displayField,
            'autocomplete_value_prop' => $identityField
        );
    }
}
