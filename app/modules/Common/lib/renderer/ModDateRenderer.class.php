<?php

use Honeybee\Core\Dat0r\Module;

class ModDateRenderer extends DefaultListValueRenderer
{
    protected $module;

    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    public function renderValue($value, $field, array &$data = array())
    {
        $translation_domain = sprintf('%s.list', $this->module->getOption('prefix'));
        $translation_domain = isset($options['domain']) ? $options['domain'] : $translation_domain;

        $mod_date = new DateTime($value);
        $modified = $mod_date->format('d.m.Y H:i');

        return AgaviContext::getInstance()->getTranslationManager()->_($modified . ' Uhr', $translation_domain);
    }
}
