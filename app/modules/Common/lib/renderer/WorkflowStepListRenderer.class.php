<?php

class WorkflowStepListRenderer extends DefaultListValueRenderer
{
    public function renderValue($value, $fieldname, array $data = array())
    {
        $tm = AgaviContext::getInstance()->getTranslationManager();

        return $tm->_('step.'.$value, sprintf('%s.list', $this->module->getOption('prefix')));
    }
}
