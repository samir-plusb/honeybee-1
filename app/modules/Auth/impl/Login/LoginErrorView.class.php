<?php

/**
 * The Auth_LoginLogin_ErrorView class handles error data presentation
 * for our various supported output types.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         ApplicationBase
 * @subpackage      Auth
 */
class Auth_LoginLogin_ErrorView extends AuthBaseView
{
    /**
     * Execute any html related presentation logic and sets up our template attributes.
     * 
     * @param       AgaviRequestDataHolder $parameters
     */
    public function executeHtml(AgaviRequestDataHolder $parameters)
    {
        parent::executeHtml($parameters);
        
        $translationManager = $this->getContext()->getTranslationManager();
        
        $this->setAttribute('_title', $translationManager->_('Login Error', 'auth.errors'));
        $this->setAttribute('error_messages', $this->getContainer()->getValidationManager()->getErrorMessages());
    }
    
    /**
     * Prepares and sets our json data on our webresponse.
     * 
     * @param       AgaviRequestDataHolder $parameters 
     * 
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function executeJson(AgaviRequestDataHolder $parameters)
    {
        $this->getContainer()->getResponse()->setContent(
            json_encode(
                array(
                    'result' => 'failure',
                    'errors' => $this->getContainer()->getValidationManager()->getErrorMessages()
                )
            )
        );
    }
    
    /**
     * Prepares and sets our json data on our console response.
     * 
     * @param       AgaviRequestDataHolder $parameters 
     */
    public function executeText(AgaviRequestDataHolder $parameters)
    {
        $translationManager = $this->getContext()->getTranslationManager();
        
        $this->getContainer()->getResponse()->setContent(
            $translationManager->_(
                'Wrong user name or password!',
                'auth.errors'
            )
        );
    }
}

?>