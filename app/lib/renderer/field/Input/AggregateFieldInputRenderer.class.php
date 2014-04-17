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

        $hasDefaultAggregate = false;
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
                    $hasDefaultAggregate = true;
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
                    $document,
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
                    $document,
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

        $start_collapsed = ($this->getField()->getOption('max', 0) > 1) ? true : false;
        if (array_key_exists('start_collapsed', $this->options) && $this->options['start_collapsed'] === true) {
            $start_collapsed = true;
        }

        return array_merge(
            parent::getPayload($document),
            array(
                'max_count' => $this->getField()->getOption('max', 0),
                'start_collapsed' => $start_collapsed,
                'has_default_aggregate' => $hasDefaultAggregate,
                'aggregates' => $aggregates,
                'aggregate_modules' => $aggregate_modules
            )
        );
    }

    protected function renderAggregateTemplate(IDocument $parentDocument, IModule $module, array $parentGroup = array())
    {
        return $this->renderAggregate($parentDocument, $module->createDocument(), $parentGroup);
    }

    protected function renderAggregate(IDocument $parentDocument, IDocument $document, array $parentGroup = array())
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
                'group' => $parentGroup,
                'parent_document' => $parentDocument
            );
            $renderer = $factory->createRenderer($field, FieldRendererFactory::CTX_INPUT, $options);
            $renderedFields[$field->getName()] = $renderer->render($document);
        }

        return $renderedFields;
    }

    protected function getWidgetOptions(IDocument $document)
    {
        $label_update_selector = null;
        if (isset($this->options['label_update_selectors'])) {
            $label_update_selector = implode(', ', $this->options['label_update_selectors']);
        } else {
            $label_update_selector = 'input[type="text"]:visible, textarea:visible';
        }
        $widgetOptions = array(
            'inputname' => $this->generateInputName($document),
            'fieldname' => $this->getField()->getName(),
            'max_count' => $this->getField()->getOption('max', 0),
            'label_update_selector' => $label_update_selector
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
