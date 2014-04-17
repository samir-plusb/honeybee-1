<?php

use Honeybee\Core\Dat0r\Module;

class BooleanListValueRenderer extends DefaultListValueRenderer
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

        return AgaviContext::getInstance()->getTranslationManager()->_($value === true ? 'yes' : 'no', $translation_domain);
    }
}
