<?php

namespace Honeybee\Core\Service;

use Honeybee\Core\Config;
use Honeybee\Core\Dat0r\Module;
use Honeybee\Core\Service\ServiceConfigurationException;
use Honeybee\Core\Service\IService;
use Honeybee\Agavi\Renderer\TwigRenderer;

/**
 * Handles templates (for modules).
 */
class TemplateService implements IService
{
    /**
     * @var Honeybee\Core\Config\ArrayConfig with given config
     */
    protected $config;

    /**
     * @var Honeybee\Core\Dat0r\Module if the service was constructed with a module
     */
    protected $module;

    /**
     * @param mixed $mixed module to get mail config from or Config\ArrayConfig instance with settings
     *
     * @throws ServiceConfigurationException if no valid module of ArrayConfig instance was given
     */
    public function __construct($mixed)
    {
        if ($mixed instanceof Module)
        {
            $this->module = $mixed;
            $config = new Config\AgaviXmlConfig(\AgaviConfig::get('core.modules_dir') . '/' . $this->module->getName() . '/config/mail.xml');
            $data = $config->toArray();
            $data['module_name'] = $this->module->getName();
            $this->config = new Config\ArrayConfig($data);
        }
        elseif ($mixed instanceof Config\ArrayConfig)
        {
            $this->config = $mixed;
        }
        elseif (is_array($mixed))
        {
            $this->config = new Config\ArrayConfig($mixed);
        }
        else
        {
            throw new ServiceConfigurationException('As PHP does not support overloading there is unfortunately no type hint for the correct type of constructor argument. Expected is a Honeybee Module or a mail ArrayConfig or even a compatible array with settings.');
        }
    }

    /**
     * Renders the template given by the identifier with the
     * specified variables. The options may be used to override
     * instantiation settings like 'output_type', 'layout' and
     * 'template_extension' for one time different uses.
     *
     * @param mixed $identifier template name (will be searched for in default template locations)
     * @param array $variables placeholders in key => value form for twig
     * @param array $options settings like 'output_type', 'layout' or 'template_extension'
     *
     * @return string rendered result
     */
    public function render($identifier, array $variables = array(), array $options = array())
    {
        $layer = $this->getLayer($identifier, $options);

        return $layer->execute(null, $variables);
    }

    /**
     * Loads the given file from common template paths and returns
     * the loaded Twig_Template.
     *
     * @param mixed $identifier
     * @param array $options settings like 'output_type', 'layout' or 'template_extension' if the config from instantiation of the service is not sufficient
     *
     * @return \Twig_Template instance
     */
    public function loadTemplate($identifier, array $options = array())
    {
        $layer = $this->getLayer($identifier, $options);

        $template = $layer->getRenderer()->loadTemplate($layer);
        
        return $template;
    }

    /**
     * Returns the first layer from the default or specified output type layout.
     *
     * @param mixed $identifier template name
     * @param array $options settings like 'output_type', 'layout' or 'template_extension' if the config from instantiation of the service is not sufficient
     *
     * @return \AgaviTemplateLayer instance (usually an \AgaviFileTemplateLayer instance)
     *
     * @throws ServiceConfigurationException in case of missing or wrong settings
     */
    public function getLayer($identifier, array $options = array())
    {
        $output_type_name = isset($options['output_type']) ? $options['output_type'] : $this->config->get('output_type', 'template');
        $layout_name = isset($options['layout']) ? $options['layout'] : $this->config->get('layout', 'default');
        $extension = isset($options['template_extension']) ? $options['template_extension'] : $this->config->get('template_extension', '.twig');

        $output_type = \AgaviContext::getInstance()->getController()->getOutputType($output_type_name);
        $layout = $output_type->getLayout($layout_name);

        if (empty($layout['layers']))
        {
            throw new ServiceConfigurationException("No layers found for layout '$layout_name' on output type '$output_type_name'.");
        }

        $layer_info = array_shift($layout['layers']); // we simply take the first layer that's available (probably 'content')

        $class_name = isset($layer_info['class']) ? $layer_info['class'] : "\AgaviFileTemplateLayer";
        if (!class_exists($class_name))
        {
            throw new ServiceConfigurationException("First layer of layout '$layout_name' on output type '$output_type_name' specifies a non-existant class: '$class_name'");
        }

        $module_name = array_key_exists('module_name', $options) ? $options['module_name'] : $this->config->get('module_name', null);
        $layer_params = array(
            'template' => $identifier,
            'extension' => $extension,
            'output_type' => $output_type_name,
            'module' => $module_name
        );

        /* hardcore fallback that leads to target paths like
         * app/project/templates/modules/../de_DE/example.mail.twig
         * instead of
         * app/project/templates/modules/${module}/de_DE/example.mail.twig
         */
        if (empty($module_name))
        {
            $layer_params['module'] = '..';
            $layer_params['directory'] = \AgaviConfig::get('core.template_dir');
        }

        $lookup_paths = array();
        if (isset($layer_info['parameters']['targets']))
        {
            $lookup_paths = array_merge($lookup_paths, $layer_info['parameters']['targets']);
        }
        $layer_params['targets'] = $lookup_paths;

        $layer = new $class_name($layer_params);
        $layer->initialize(\AgaviContext::getInstance(), $layer_params);

        $renderer_name = isset($layer_info['renderer']) ? $layer_info['renderer'] : $this->config->get('renderer', 'twig');
        $layer->setRenderer($output_type->getRenderer($renderer_name));

        if (!$layer->getRenderer() instanceof TwigRenderer)
        {
            throw new ServiceConfigurationException("The default layer renderer of layout '$layout_name' on output type '$output_type_name' is not an instance of the Honeybee TwigRenderer. At the moment only Twig is supported as a renderer for mails.");
        }

        return $layer;
    }
}
