<?php

use Dat0r\Core\Document\IDocument;

class AclRoleSelectFieldInputRenderer extends SelectFieldInputRenderer
{
    protected function getSelectionOptions(IDocument $document)
    {
        $context = AgaviContext::getInstance();
        $user = $context->getUser();
        $translation_manager = $context->getTranslationManager();

        $select_values = array();
        foreach ($user->getAvailableRoles() as $role) {
            $select_values[$role] = $translation_manager->_($role, 'user.roles');
        }
        ksort($select_values);
        return $select_values;
    }
}
