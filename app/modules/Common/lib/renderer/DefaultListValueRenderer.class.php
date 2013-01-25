<?php

use Honeybee\Core\Dat0r\Module;

class DefaultListValueRenderer implements IListValueRenderer
{
    protected $module;
    
    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    public function renderValue($value, $fieldname, array $data = array())
    {
        return $value;
    }

    public function renderTemplate(ListField $field, $options = array())
    {
        $user = AgaviContext::getInstance()->getUser();
        $loader = new Twig_Loader_Filesystem($this->getTemplateDirectory());
        $twig = new Twig_Environment($loader);

        $rendered = $twig->render($this->getTemplateFilename(), array('field' => $field));
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
