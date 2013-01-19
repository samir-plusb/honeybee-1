<?php

class DefaultListValueRenderer implements IListValueRenderer
{
    protected $module;
    
    public function __construct(HoneybeeModule $module)
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
        ob_start();
        include $this->getTemplatePath();
        $rendered = ob_get_contents();
        ob_end_clean();
        return $rendered;
    }

    protected function getTemplatePath()
    {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Default.tpl.php';
    }
}

?>
