<?php

/**
 * The Auth_Logout_LogoutSuccessView class handles success data presentation
 * for the various supported output types we want to support for our Auth_LogoutAction.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Auth
 * @subpackage      Mvc
 */
class Auth_Logout_LogoutSuccessView extends AuthBaseView
{
    /**
     * Execute any html related presentation logic and sets up our template attributes.
     * 
     * @param       AgaviRequestDataHolder $parameters 
     * 
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeHtml(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        /* @var $context AgaviContext */
        $context = $this->getContext();
        /* @var $user AgaviSecurityUser */
        $user = $context->getUser();
        /* @var $routing AgaviWebRouting */
        $routing = $context->getRouting();
        /* @var $translationManager AgaviTranslationManager */
        $translationManager = $context->getTranslationManager();
        
        $user->addIncident($translationManager->_("You have been logged out"), AgaviLogger::INFO);
        
        $this->getResponse()->setRedirect($routing->gen('login'));
    }
    
    /**
     * Prepares and sets our json data on our webresponse.
     * 
     * @param       AgaviRequestDataHolder $parameters 
     * 
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeJson(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $this->getContainer()->getResponse()->setContent(
            json_encode(
                array(
                    'result'  => 'success',
                    'message' => 'You have been successfully logged out.'
                )
            )
        );
    }
    
    /**
     * Prepares and sets our json data on our console response.
     * 
     * @param       AgaviRequestDataHolder $parameters 
     * 
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeText(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $this->getContainer()->getResponse()->setContent(
            "You have been successfully logged out.\n"
        );
    }
}

?>