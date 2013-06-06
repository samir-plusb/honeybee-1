<?php

/**
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @package         User
 */
class User_Login_LoginErrorView extends UserBaseView
{
    public function executeBinary(AgaviRequestDataHolder $request_data) // @codingStandardsIgnoreEnd
    {
        // @easter-egg Return 'I am a teapot' for people,
        //  that managed to provide data leading into this code path.
        $this->getContext()->getController()->getGlobalResponse()->setHttpStatusCode(418);
    }

    /**
     * Execute any html related presentation logic and sets up our template attributes.
     *
     * @param AgaviRequestDataHolder $request_data
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeHtml(AgaviRequestDataHolder $request_data) // @codingStandardsIgnoreEnd
    {
        parent::setupHtml($request_data);

        $this->setAttribute('_title', $this->getContext()->getTranslationManager()->_('Login Error', 'user.messages'));
        $this->setAttribute('error_messages', $this->getContainer()->getValidationManager()->getErrorMessages());

        $this->getLayer('content')->setTemplate('Login/LoginInput');
    }

    /**
     * Prepares and sets our json data on our webresponse.
     *
     * @param AgaviRequestDataHolder $request_data
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeJson(AgaviRequestDataHolder $request_data) // @codingStandardsIgnoreEnd
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
     * @param AgaviRequestDataHolder $request_data
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeConsole(AgaviRequestDataHolder $request_data) // @codingStandardsIgnoreEnd
    {
        $this->getContainer()->getResponse()->setContent(
            $this->getContext()->getTranslationManager()->_(
                'Wrong user name or password!',
                'user'
            )
        );
    }
}
