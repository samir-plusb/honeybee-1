<?php

use Dat0r\Core\Field\ReferenceField;
use Honeybee\Core\Dat0r\Document;

class ReferenceFieldInputRenderer extends FieldInputRenderer
{
    protected function getWidgetType(Document $document)
    {
        return 'widget-reference';
    }

    protected function getTemplateName()
    {
        return "Reference.tpl.twig";
    }

    protected function getWidgetOptions(Document $document)
    {
        $parentOptions = parent::getWidgetOptions($document);

        $tm = AgaviContext::getInstance()->getTranslationManager();
        $references = $this->getField()->getOption(ReferenceField::OPT_REFERENCES);

        $moduleSettings = array();
        foreach ($references as $reference)
        {
            $moduleImplementor = $reference[ReferenceField::OPT_MODULE];
            $module = $moduleImplementor::getInstance();
            $moduleSettings[$module->getOption('prefix')] = array(
                'id_field' => $reference[ReferenceField::OPT_IDENTITY_FIELD],
                'display_field' => $reference[ReferenceField::OPT_DISPLAY_FIELD]
            );
        }

        $tags = array();
        if ($documents = $document->getValue($this->getField()->getName()))
        {
            foreach ($documents as $refDocument)
            {
                $settings = $moduleSettings[$refDocument->getModule()->getOption('prefix')];
                $text = $refDocument->getValue($settings['display_field']);
                $tags[] = array(
                    'text' => $text,
                    'id' => $refDocument->getValue($settings['id_field']),
                    'module_prefix' => $refDocument->getModule()->getOption('prefix'),
                    'label' => $tm->_($refDocument->getModule()->getName()) . ': ' . $text
                );
            }
        }
        $routing = AgaviContext::getInstance()->getRouting();
        $maxCount = (int)$this->getField()->getOption(ReferenceField::OPT_MAX_REFERENCES, 0);
        return array_merge($parentOptions, array(
            'event_origin' => $routing->getBaseHref(),
            'autocomplete' => TRUE,
            'autocomp_mappings' => $this->buildAutoCompleteOptions($document),
            'enable_inline_create' => $this->getField()->getOption('enable_inline_create', false),
            'fieldname' => $this->generateInputName($document),
            'realname' => $this->getField()->getName(),
            'max' => $maxCount,
            'tags' => $tags,
            'tpl' => 'Stacked',
            'texts' =>  array(
                'placeholder' => $tm->_('Verknüpfen'),
                'searching' => $tm->_('Suche ...'),
                'too_short' => $tm->_('Bitte mindestens ein Zeichen eingeben'),
                'too_long' => $tm->_('maximal erlaubte Anzahl an Verknüpfungen erreicht'),
                'no_results' => $tm->_('Keine passenden Ergebnisse gefunden'),
                'inline_create_label' => $tm->_('Verknüpfungsziel direkt erzeugen')
            )
        ));
    }

    protected function buildAutoCompleteOptions(Document $document)
    {
        $tm = AgaviContext::getInstance()->getTranslationManager();
        $references = $this->getField()->getOption(ReferenceField::OPT_REFERENCES);
        
        $autoCompleteMappings = array();

        foreach ($references as $reference)
        {
            $referenceModuleClass = $reference['module'];
            $displayField = $reference[ReferenceField::OPT_DISPLAY_FIELD];
            $identityField = $reference[ReferenceField::OPT_IDENTITY_FIELD];
            $referencedModule = $referenceModuleClass::getInstance();
            $modulePrefix = $referencedModule->getOption('prefix');
            $suggestRouteName = sprintf('%s.suggest', $modulePrefix);
            $listRouteName = sprintf('%s.list', $modulePrefix);
            $createRouteName = sprintf('%s.edit', $modulePrefix);

            $autoCompleteMappings[$modulePrefix] = array(
                'display_field' => $displayField,
                'identity_field' => $identityField,
                'module_label' => $tm->_($referencedModule->getName(), 'modules.labels'),
                'list_url' => htmlspecialchars_decode(
                    urldecode($this->getRouteLink($listRouteName, array(
                        'referenceModule' => $document->getModule()->getName(),
                        'referenceField' => $this->getField()->getName(),
                        'offset' => 0,
                        'limit' => 10
                    )))
                ),
                'uri' => htmlspecialchars_decode(
                    urldecode($this->getRouteLink($suggestRouteName, array(
                        'term' => '{PHRASE}',
                        'display_field' => $displayField,
                        'identity_field' => $identityField
                    )))
                )
            );
            
            if ($this->getField()->getOption('enable_inline_create', false))
            {
                $autoCompleteMappings[$modulePrefix] = array_merge(
                    $autoCompleteMappings[$modulePrefix],
                    array(
                        'create_label' => $tm->_($modulePrefix.'_create', $modulePrefix.'.rendering.input.document'),
                        'success_label' => $tm->_($modulePrefix.'_create_success', $modulePrefix.'.rendering.input.document'),
                        'create_url' => htmlspecialchars_decode(
                            urldecode($this->getRouteLink($createRouteName))
                        )
                    )
                );
            }
        }

        return $autoCompleteMappings;
    }
}
