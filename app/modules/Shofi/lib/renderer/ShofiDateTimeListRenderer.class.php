<?php

class ShofiDateTimeListRenderer extends DefaultListValueRenderer
{
    public function renderValue($value, $fieldname, array $data = array())
    {
        $translationManager = AgaviContext::getInstance()->getTranslationManager();
        return $translationManager->_d($value, 'shofi.datetime');
    }
}
