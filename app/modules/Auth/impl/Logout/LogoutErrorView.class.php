<?php

/**
 * The Auth_Logout_LogoutErrorView class handles error data presentation
 * for the various supported output types we want to support for our Auth_LogoutAction.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         ApplicationBase
 * @subpackage      Auth/Logout
 */
class Auth_Logout_LogoutErrorView extends AuthBaseView
{
	/**
     * Execute any html related presentation logic and sets up our template attributes.
     * 
     * @param       AgaviRequestDataHolder $parameters 
     */
	public function executeHtml(AgaviRequestDataHolder $parameters)
	{
	    parent::setupHtml();
		
 		// set our template
 		$this->appendLayer($this->createLayer('AgaviFileTemplateLayer', 'content'));

		// set the title
		$this->setAttribute('_title', 'Logout Action');
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
                    'result'  => 'error',
                    'message' => 'An unexpected error occured during logout.'
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
            "An unexpected error occured during logout.\n"
        );
    }
}

?>