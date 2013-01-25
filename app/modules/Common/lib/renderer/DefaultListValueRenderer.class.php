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
        $loader = new Twig_Loader_Filesystem($this->getTemplatePath());
        $twig = new Twig_Environment($loader);

        $rendered = $twig->render('Default.tpl.twig', array('field' => $field));
        return $rendered;
    }

    protected function getTemplatePath()
    {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR;
    }
}

?>
