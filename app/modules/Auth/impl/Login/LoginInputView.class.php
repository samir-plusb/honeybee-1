<?php

class Auth_Login_LoginInputView extends AuthBaseView
{
    /**
     * Execute any presentation logic and set template attributes.
     */
    public function executeHtml(AgaviRequestDataHolder $parameters)
    {
        parent::setupHtml($parameters);

        $tm = $this->getContext()->getTranslationManager();

        if ($this->getContext()->getRequest()->hasAttributeNamespace('org.agavi.controller.forwards.login'))
        {
            // we were redirected to the login form by the controller because the requested action required security
            // so store the input URL in the session for a redirect after login
            $url = $this->getContext()->getRequest()->getUrl();

            /**
             * Prevent redirecting to strange urls after logging in (js, css files, ...)
             */
            if (!preg_match('#\.(jpe?g|css|js|png|gif|ico|swf)\??#', $url))
            {
                $this->getContext()->getUser()->setAttribute('redirect', $url, 'de.berlinonline.contentworker.login');
            }
        }
        else
        {
            // clear the redirect URL just to be sure 
            $this->getContext()->getUser()->removeAttribute('redirect', 'de.berlinonline.contentworker.login');
        }

        // set the title
        $this->setAttribute('_title', $tm->_('Login', 'auth.messages'));
    }
    
    public function executeJson(AgaviRequestDataHolder $rd)
    {
        if (null != ($container = $this->attemptForward($rd)))
        {
            return $container;
        }

        $this->getContainer()->getResponse()->setContent(
            json_encode(
                array(
                    'result'  => 'success',
                    'message' => 'You may post a username and a password to this uri in order to login to the application.'
                )
            )
        );
    }

    public function executeText(AgaviRequestDataHolder $rd)
    {
        $this->getContainer()->getResponse()->setContent(
            $tm->_(
                'Please provide username and password as commandline arguments when calling secure actions. Use --username {user} --password {pass}.',
                'auth.messages'
            )
        );
    }
}

?>