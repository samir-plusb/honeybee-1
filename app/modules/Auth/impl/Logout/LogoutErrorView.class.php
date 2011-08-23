<?php

class Auth_Logout_LogoutErrorView extends AuthBaseView
{
	/**
	 * Execute any presentation logic and set template attributes.
	 */
	public function executeHtml(AgaviRequestDataHolder $parameters)
	{
	    parent::setupHtml();
		
 		// set our template
 		$this->appendLayer($this->createLayer('AgaviFileTemplateLayer', 'content'));

		// set the title
		$this->setAttribute('_title', 'Logout Action');
	}
}

?>