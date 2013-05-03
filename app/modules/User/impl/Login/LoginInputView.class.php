<?php

/**
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @package         User
 */
class User_Login_LoginInputView extends UserBaseView
{
    public function executeBinary(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $this->memoizeLocationForLaterRedirect();
        $this->getContext()->getController()->getGlobalResponse()->setHttpStatusCode(401);
    }

    /**
     * Execute any html related presentation logic and sets up our template attributes.
     *
     * @param       AgaviRequestDataHolder $parameters
     */
    public function executeHtml(AgaviRequestDataHolder $parameters)
    {
        parent::setupHtml($parameters);

        $translationManager = $this->getContext()->getTranslationManager();
        $this->memoizeLocationForLaterRedirect();
        // set the title
        $this->setAttribute('_title', $translationManager->_('Login', 'user.ui'));
    }

    /**
     * Prepares and sets our json data on our webresponse.
     *
     * @param       AgaviRequestDataHolder $parameters
     */
    public function executeJson(AgaviRequestDataHolder $parameters)
    {
        if (NULL != ($container = $this->attemptForward($parameters)))
        {
            return $container;
        }

        $this->getContainer()->getResponse()->setContent(
            json_encode(
                array(
                    'result'  => 'success',
                    'message' => 'You may post a username and a password' .
                                 'to this uri in order to login to the application.'
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
        $translationManager = $this->getContext()->getTranslationManager();

        $this->getContainer()->getResponse()->setContent(
            $translationManager->_(
                'Please provide username and password as commandline arguments when calling secure actions.' .
                'Use --username {user} --password {pass}.',
                'user.messages'
            )
        );
    }

    protected function memoizeLocationForLaterRedirect()
    {
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
    }
}