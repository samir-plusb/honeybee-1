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

        $placeholder = $tm->_($fieldName . '_placeholder', $td);
        if ($placeholder === $fieldName . '_placeholder') {
            $placeholder = null;
        }

        return array(
            'fieldName' => $fieldName,
            'fieldKey' => $this->options['field_key'],
            'mandatory' => $this->getField()->getOption('mandatory', false),
            'field' => $this->getField(),
            'inputName' => $this->generateInputName($document),
            'fieldId' => $this->generateInputId($document),
            'fieldValue' => $this->renderFieldValue($document),
            'widgetType' => $widgetType,
            'hasWidget' => ($widgetType !== NULL),
            'widgetOptions' => $this->renderWidgetOptions($document),
            'readonly' => $this->isReadonly($document),
            'placeholder' => $placeholder,
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

    protected function generateInputId(IDocument $document)
    {
        return sprintf(
            'f-input-%s',
            md5($this->generateInputName($document))
        );
    }

    protected function getWidgetOptions(IDocument $document)
    {
        $prefix = $document->getModule()->getOption('prefix');
        if (isset($this->options['parent_document'])) {
            $prefix = $this->options['parent_document']->getModule()->getOption('prefix');
        }
        $widgetSettings = AgaviConfig::get(sprintf('%s.input_widgets', $prefix));
        $fieldkey = $this->options['field_key'];
        $routing = AgaviContext::getInstance()->getRouting();

        $widgetOptions = array(
            'event_origin' => $routing->getBaseHref(),
            'readonly' => $this->isReadonly($document),
            'autobind' => true,
            'field_id' => $this->generateInputId($document),
            'fieldname' => $this->generateInputName($document),
            'realname' => $this->getField()->getName()
        );

        if (isset($widgetSettings[$fieldkey]))
        {
            $widgetDef = $widgetSettings[$fieldkey];
            $widgetOptions = array_merge($widgetDef['options'], $widgetOptions);
            $widgetOptions['field_value'] = $document->getValue($this->getField()->getName());
        }

        return $widgetOptions;
    }

    protected function generateInputName(IDocument $document)
    {
        $name = '';

        if (isset($this->options['fieldpath']))
        {
            $first = true;
            $fieldpath_parts = explode('.', $this->options['fieldpath']);
            $name_parts = array();
            foreach($fieldpath_parts as $part)
            {
                if ($first)
                {
                    $name_parts = array($document->getModule()->getOption('prefix'));
                    $group_parts = isset($this->options['group']) ? $this->options['group'] : null;
                    if (is_array($group_parts))
                    {
                        $name_parts = $group_parts;
                    }
                    $name_parts[] = $part;
                    $first = FALSE;
                }
                else
                {
                    $name_parts[] = $part;
                }
            }
            $name = $this->generateArrayPath($name_parts);
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

    protected function generateArrayPath(array $path_parts)
    {
        $first_part = array_shift($path_parts);
        return sprintf('%s[%s]', $first_part, implode('][', $path_parts));
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
