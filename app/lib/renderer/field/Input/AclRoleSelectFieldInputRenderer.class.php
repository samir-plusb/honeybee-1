<?php

use Honeybee\Core\Dat0r\Document;

class AclRoleSelectFieldInputRenderer extends FieldInputRenderer
{
    protected function getPayload(Document $document)
    {
        $context = AgaviContext::getInstance();
        $user = $context->getUser();
        $translationManager = $context->getTranslationManager();

        $selectVals = array();
        foreach ($user->getAvailableRoles() as $role)
        {
            $selectVals[$role] = $translationManager->_($role, 'user.roles');
        }

        $payload = parent::getPayload($document);
        $payload['selectValues'] = $selectVals;

        return $payload;
    }

    protected function getTemplateName()
    {
        return "Select.tpl.twig";
    }
}
