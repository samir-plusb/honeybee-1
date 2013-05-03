<?php

/**
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @package         User
 */
class User_Login_LoginInputView extends UserBaseView
{
    /**
     * Execute any html related presentation logic and sets up our template attributes.
     *
     * @param       AgaviRequestDataHolder $parameters
     */
    public function executeHtml(AgaviRequestDataHolder $parameters)
    {
        parent::setupHtml($parameters);

        $translationManager = $this->getContext()->getTranslationManager();
        $this->setAttribute('_title', $translationManager->_('Login', 'user.ui'));
    }

    /**
     * Prepares and sets our json data on our webresponse.
     *
     * @param       AgaviRequestDataHolder $parameters
     */
    public function executeJson(AgaviRequestDataHolder $parameters)
    {
        $jsonData = json_encode(
            array(
                'result'  => 'success',
                'message' => 'You may post a username and a password to this uri in order to login to the application.'
            )
        );

        $this->getContainer()->getResponse()->setContent($jsonData);
        $this->getContext()->getController()->getGlobalResponse()->setHttpStatusCode(401);
    }

    public function executeBinary(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $this->getContext()->getController()->getGlobalResponse()->setHttpStatusCode(401);
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

        return 1; // falsey shell return code
    }
}
