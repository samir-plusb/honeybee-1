<?php

use Dat0r\Core\Document\IDocument;

class AclRoleSelectFieldInputRenderer extends SelectFieldInputRenderer
{
    protected function getSelectionOptions(IDocument $document)
    {
        $context = AgaviContext::getInstance();
        $user = $context->getUser();
        $translationManager = $context->getTranslationManager();

        $selectVals = array();
        foreach ($user->getAvailableRoles() as $role)
        {
            $selectVals[$role] = $translationManager->_($role, 'user.roles');
        }

        return $selectVals;
    }
}
