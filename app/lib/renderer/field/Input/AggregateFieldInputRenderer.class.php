<?php

use Honeybee\Core\Dat0r\Document;
use Dat0r\Core\Document\IDocument;
use Dat0r\Core\Module\IModule;

class AggregateFieldInputRenderer extends FieldInputRenderer
{
    protected function getPayload(IDocument $document)
    {
        $aggregateDocuments = $document->getValue($this->getField()->getName());
        $aggregates = array();

        if (count($aggregateDocuments) === 0 && isset($this->options['default_aggregate']))
        {
            $defaultAggregate = $this->options['default_aggregate'];
            foreach ($this->getField()->getAggregateModules() as $aggregateModule)
            {
                $typeParts = explode('\\', $aggregateModule->getDocumentType());
                $documentType = array_pop($typeParts);
                if ($documentType === $defaultAggregate)
                {
                    $aggregateDocuments->add($aggregateModule->createDocument());
                }
            }
        }

        foreach ($aggregateDocuments as $pos => $aggregateDocument)
        {
            $aggregates[] = array(
                'name' => $aggregateDocument->getModule()->getName(),
                'label' => $aggregateDocument->getModule()->getName(),
                'type' => get_class($aggregateDocument),
                'rendered_fields' => $this->renderAggregate(
                    $aggregateDocument,
                    array(
                        $document->getModule()->getOption('prefix'),
                        $this->getField()->getName(),
                        $pos
                    )
                ),
                'type_field' => sprintf(
                    '%s[%s][type]',
                    $document->getModule()->getOption('prefix'),
                    implode('][', array(
                        $this->getField()->getName(),
                        $pos
                    ))
                )
            );
        }

        $translation = AgaviContext::getInstance()->getTranslationManager();
        $aggregate_modules = array();

        foreach ($this->getField()->getAggregateModules() as $pos => $aggregateModule)
        {
            $aggregate_modules[] = array(
                'name' => $aggregateModule->getName(),
                'label' => $aggregateModule->getName(),
                'type' => $aggregateModule->getDocumentType(),
                'rendered_fields' => $this->renderAggregateTemplate(
                    $aggregateModule,
                    array(
                        $document->getModule()->getOption('prefix'),
                        $this->getField()->getName(),
                        $pos
                    )
                ),
                'type_field' => sprintf(
                    '%s[%s][type]',
                    $document->getModule()->getOption('prefix'),
                    implode('][', array(
                        $this->getField()->getName(),
                        $pos
                    ))
                )
            );
        }

        return array_merge(
            parent::getPayload($document),
            array(
                'aggregates' => $aggregates,
                'aggregate_modules' => $aggregate_modules
            )
        );
    }

    protected function renderAggregateTemplate(IModule $module, array $parentGroup = array())
    {
        return $this->renderAggregate($module->createDocument(), $parentGroup);
    }

    protected function renderAggregate(IDocument $document, array $parentGroup = array())
    {
        $factory = new FieldRendererFactory($document->getModule());

        $renderedFields = array();
        $fieldsToRender = array();

        foreach ($document->getModule()->getFields() as $field)
        {
            $options = array(
                'fieldpath' => $field->getName(),
                'field_key' => $this->getField()->getName().'.'.strtolower($document->getModule()->getName()).'.'.$field->getName(),
                'translation_domain' => $parentGroup[0] . '.rendering.input.field',
                'group' => $parentGroup
            );
            $renderer = $factory->createRenderer($field, FieldRendererFactory::CTX_INPUT, $options);
            $renderedFields[$field->getName()] = $renderer->render($document);
        }

        return $renderedFields;
    }

    protected function getWidgetOptions(IDocument $document)
    {
        $widgetOptions = array(
            'inputname' => $this->generateInputName($document),
            'fieldname' => $this->getField()->getName()
        );

        return array_merge(parent::getWidgetOptions($document), $widgetOptions);
    }

    protected function getTemplateName()
    {
        return "Aggregate.tpl.twig";
    }

    protected function getWidgetType(IDocument $document)
    {
        return 'widget-aggregate';
    }
}
