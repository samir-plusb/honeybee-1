<?php

/**
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @package         User
 */
class User_Login_LoginErrorView extends UserBaseView
{
    public function executeBinary(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        // @easter-egg Return 'I am a teapot' for people,
        //  that managed to provide data leading into this code path.
        $this->getContext()->getController()->getGlobalResponse()->setHttpStatusCode(418);
    }

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
        parent::setupHtml($parameters);

        $translationManager = $this->getContext()->getTranslationManager();

        $this->setAttribute('_title', $translationManager->_('Login Error', 'user.messages'));
        $this->setAttribute('error_messages', $this->getContainer()->getValidationManager()->getErrorMessages());

        $this->getLayer('content')->setTemplate('Login/LoginInput');
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
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeConsole(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $translationManager = $this->getContext()->getTranslationManager();

        $this->getContainer()->getResponse()->setContent(
            $translationManager->_(
                'Wrong user name or password!',
                'user'
            )
        );
    }
}
