<?php

/**
 * The Auth_Logout_LogoutSuccessView class handles success data presentation
 * for the various supported output types we want to support for our Auth_LogoutAction.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         ApplicationBase
 * @subpackage      Auth/Logout
 */
class Auth_Logout_LogoutSuccessView extends AuthBaseView
{
    /**
     * Execute any html related presentation logic and sets up our template attributes.
     * 
     * @param       AgaviRequestDataHolder $parameters 
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
        
        $user->addIncident($tm->_("You have been logged out"), AgaviLogger::INFO);
        
        $this->getResponse()->setRedirect($ro->gen('login'));
    }
    
    /**
     * Prepares and sets our json data on our webresponse.
     * 
     * @param       AgaviRequestDataHolder $parameters 
     */
    public function executeJson(AgaviRequestDataHolder $rd)
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
     */
    public function executeText(AgaviRequestDataHolder $rd)
    {
        $this->getContainer()->getResponse()->setContent(
            "You have been successfully logged out.\n"
        );
    }
}

?>