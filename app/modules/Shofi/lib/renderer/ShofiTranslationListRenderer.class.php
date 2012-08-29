<?php

class ShofiTranslationListRenderer extends DefaultListValueRenderer
{
    public function renderValue($value, $fieldname, array $data = array())
    {
        $translationManager = AgaviContext::getInstance()->getTranslationManager();
        return $translationManager->_($value, 'shofi.list');
    }
}
