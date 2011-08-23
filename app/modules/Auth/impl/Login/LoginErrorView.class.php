<?php

class Auth_LoginLogin_ErrorView extends Auth_Login_LoginInputView
{
	/**
	 * Execute any presentation logic and set template attributes.
	 */
	public function executeHtml(AgaviRequestDataHolder $parameters)
	{
	    parent::executeHtml($parameters);
        
	    $tm = $this->getContext()->getTranslationManager();
		$this->setAttribute('_title', $tm->_('Login Error', 'auth.errors'));
		
	    $this->setAttribute('error_messages', $this->getContainer()->getValidationManager()->getErrorMessages());
	}
	
	public function executeJson(AgaviRequestDataHolder $rd)
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
	
    public function executeText(AgaviRequestDataHolder $rd)
    {
        $this->getContainer()->getResponse()->setContent(
            $tm->_(
                'Wrong user name or password!',
                'auth.errors'
            )
        );
    }
}

?>