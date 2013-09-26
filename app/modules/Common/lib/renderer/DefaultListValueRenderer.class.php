<?php

use Honeybee\Core\Dat0r\Module;

class DefaultListValueRenderer implements IListValueRenderer
{
    protected $module;

    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    public function renderValue($value, $field, array $data = array())
    {
        $rendererDef = $field->getRenderer();
        $options = isset($rendererDef['options']) ? $rendererDef['options'] : array();
        if (is_array($value))
        {
            $value = strip_tags(implode(', ', $value));
        }
        else
        {
            $value = strip_tags($value);
            // shorten long strings to 30 words...
            if (mb_strlen($value) >= 255)
            {
                return implode(' ', array_slice(explode(' ', $value), 0, 30)) . ' [...]';
            }
        }

        if (empty($value))
        {
            $value = isset($options['default']) ? $options['default'] : $value;
        }

        if (isset($options['translate']) && true === $options['translate'])
        {
            $translation_domain = sprintf('%s.list', $this->module->getOption('prefix'));
            $translation_domain = isset($options['domain']) ? $options['domain'] : $translation_domain;
            $value = AgaviContext::getInstance()->getTranslationManager()->_($value, $translation_domain);
        }

        return $value;
    }

    public function renderTemplate(ListField $field, $options = array())
    {
        $user = AgaviContext::getInstance()->getUser();
        $loader = new Twig_Loader_Filesystem($this->getTemplateDirectory());
        $twig = new Twig_Environment($loader);

        $rendered = $twig->render($this->getTemplateFilename(), array('user' => $user, 'field' => $field));
        return $rendered;
    }

    protected function getTemplateDirectory()
    {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR;
    }

    protected function getTemplateFilename()
    {
        return 'Default.tpl.twig';
    }
}

?>
