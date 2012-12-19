<?php

use Dat0r\Core\Runtime\Field\IField;

class FieldRendererFactory
{
    const CTX_INPUT = 'Input';

    private $module;

    public function __construct(HoneybeeModule $module)
    {
        $this->module = $module;
    }

    public function createRenderer(IField $field, $renderingContext)
    {
        $factoryInfo = $this->determineImplementor($field, $renderingContext);

        return new $factoryInfo['implementor']($field, $factoryInfo['options']);
    }   

    protected function determineImplementor(IField $field, $renderingContext)
    {
        $prefix = $this->getModule()->getOption('prefix');
        $settingName = sprintf('%s.rendering_config', $prefix);
        $rendererConfig = AgaviConfig::get($settingName);

        $fieldname = $field->getName();
        if (isset($rendererConfig[$fieldname]) && isset($rendererConfig[$fieldname]['input']))
        {
            return array(
                'implementor' => is_array($rendererConfig[$fieldname]['input'])
                    ? $rendererConfig[$fieldname]['input']['type']
                    : $rendererConfig[$fieldname]['input'],
                'options' => is_array($rendererConfig[$fieldname]['input']) 
                    && isset($rendererConfig[$fieldname]['input']['options'])
                    ? $rendererConfig[$fieldname]['input']['options']
                    : array()
            );
        }

        $buildImplementor = function($fieldClass, $context)
        {
            $parts = explode('\\', $fieldClass);
            return sprintf('%s%sRenderer', $parts[count($parts) - 1], $context);
        };
        $implementor = $buildImplementor(get_class($field), $renderingContext);
        $curFieldClass = get_class($field);

        while (! class_exists($implementor) && FALSE !== $curFieldClass)
        {
            $curFieldClass = get_parent_class($curFieldClass);
            $implementor = $buildImplementor($curFieldClass, $renderingContext);
        }

        if (! class_exists($implementor))
        {
            throw new Exception(
                sprintf(
                    "Unable to find %s renderer for field type %s.", 
                    $renderingContext, get_class($field)
                )
            );
        }

        return array('implementor' => $implementor, 'options' => array());
    }

    protected function getModule()
    {
        return $this->module;
    }
}
