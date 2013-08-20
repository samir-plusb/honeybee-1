<?php

use Dat0r\Core\Document\IDocument;
use Dat0r\Core\Field\IField;

class FieldInputRenderer extends FieldRenderer
{
    public function __construct(IField $field, array $options = array())
    {
        if (! isset($options['field_key']))
        {
            $options['field_key'] = $field->getName();
        }

        parent::__construct($field, $options);
    }

    protected function doRender(IDocument $document)
    {
        $payload = $this->getPayload($document);

        if (preg_match('/\.twig$/', $this->getTemplateName()))
        {
            return $this->renderTwig($payload);
        }
        else
        {
            extract($payload);
            ob_start();

            include $this->getTemplate();

            $content = ob_get_contents();
            ob_end_clean();

            return $content;
        }
    }

    protected function getPayload(IDocument $document)
    {
        $tm = $this->getTranslationManager();
        $td = $this->getTranslationDomain($document);

        $fieldName = $this->getField()->getName();
        $widgetType = $this->getWidgetType($document);

        return array(
            'fieldName' => $fieldName,
            'fieldKey' => $this->options['field_key'],
            'field' => $this->getField(),
            'inputName' => $this->generateInputName($document),
            'fieldId' => 'input-' . $fieldName,
            'placeholder' => $this->getField()->getOption('placesholder', ''),
            'fieldValue' => $this->renderFieldValue($document),
            'widgetType' => $widgetType,
            'hasWidget' => ($widgetType !== NULL),
            'widgetOptions' => $this->renderWidgetOptions($document),
            'readonly' => $this->isReadonly($document),
            'tm' => $tm,
            'td' => $td
        );
    }

    protected function getWidgetType(IDocument $document)
    {
        $prefix = $document->getModule()->getOption('prefix');

        if (isset($this->options['group']))
        {
            $prefix = $this->options['group'][0];
        }
        $widgetSettings = AgaviConfig::get(sprintf('%s.input_widgets', $prefix));
        $fieldkey = $this->options['field_key'];
        $widget = NULL;
        if (isset($widgetSettings[$fieldkey]))
        {
            $widgetDef = $widgetSettings[$fieldkey];
            $widget = $widgetDef['type'];
        }

        return $widget;
    }

    protected function renderWidgetOptions(IDocument $document)
    {
        return htmlspecialchars(
            json_encode($this->getWidgetOptions($document))
        );
    }

    protected function renderFieldValue(IDocument $document)
    {
        $value = '';
        if (isset($this->options['fieldpath']))
        {
            $fieldParts = explode('.', $this->options['fieldpath']);
            $module = $document->getModule();
            $curDoc = $document;

            while (count($fieldParts) > 1 && $curDoc)
            {
                $fieldName = array_shift($fieldParts);
                $curDoc = $curDoc->getValue($fieldName)->first();
            }

            $value = $curDoc ? $curDoc->getValue($fieldParts[0]) : '';
        }
        else
        {
            $value = $document->getValue($this->getField()->getName());
        }

        return is_scalar($value) ? $value : '';
    }

    protected function getWidgetOptions(IDocument $document)
    {
        $prefix = $document->getModule()->getOption('prefix');
        $widgetSettings = AgaviConfig::get(sprintf('%s.input_widgets', $prefix));
        $fieldkey = $this->options['field_key'];
        $widgetOptions = array();

        if (isset($widgetSettings[$fieldkey]))
        {
            $widgetDef = $widgetSettings[$fieldkey];
            $widgetOptions = $widgetDef['options'];
        }

        $widgetOptions['readonly'] = $this->isReadonly($document);
        $widgetOptions['autobind'] = TRUE;

        return $widgetOptions;
    }

    protected function generateInputName(IDocument $document)
    {
        $name = '';

        if (isset($this->options['fieldpath']))
        {
            $first = true;
            $fieldpath_parts = explode('.', $this->options['fieldpath']);
            foreach($fieldpath_parts as $part)
            {
                if ($first)
                {
                    $group = $document->getModule()->getOption('prefix');
                    $group_parts = isset($this->options['group']) ? $this->options['group'] : null;
                    if (is_array($group_parts))
                    {
                        $first_part = array_shift($group_parts);
                        $group = sprintf('%s[%s]', $first_part, implode('][', $group_parts));
                    }
                    $name = sprintf('%s[%s]', $group, $part);
                    $first = FALSE;
                }
                else
                {
                    $name .= sprintf('[%s]', $part);
                }
            }
        }
        else
        {
            $name = sprintf(
                '%s[%s]',
                $document->getModule()->getOption('prefix'),
                $this->getField()->getName()
            );
        }

        return $name;
    }

    protected function getTemplateName()
    {
        return "Default.tpl.twig";
    }

    protected function getTemplateDirectory()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
    }

    public function getTranslationManager()
    {
        return AgaviContext::getInstance()->getTranslationManager();
    }

    public function getTranslationDomain(IDocument $document)
    {
        if (isset($this->options['translation_domain']))
        {
            return $this->options['translation_domain'];
        }
        else
        {
            return parent::getTranslationDomain($document) . '.input.field';
        }
    }
}
