<?php

class Auth_Logout_LogoutSuccessView extends AuthBaseView
{
    /**
     * Execute any presentation logic and set template attributes.
     */
    public function executeHtml(AgaviRequestDataHolder $parameters)
    {
        /* @var $context AgaviContext */
        $context = $this->getContext();
        /* @var $user AgaviSecurityUser */
        $user = $context->getUser();
        /* @var $ro AgaviWebRouting */
        $ro = $context->getRouting();
        /* @var $tm AgaviTranslationManager */
        $tm = $context->getTranslationManager();
        
        $user->addIncident($tm->_("You have been logged out", 'cms.auth'), AgaviLogger::INFO);
        
        $this->getResponse()->setRedirect($ro->gen('login'));
    }
}

?>