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
     * @param AgaviRequestDataHolder $request_data
     */
    public function executeHtml(AgaviRequestDataHolder $request_data)
    {
        parent::setupHtml($request_data);

        $this->setAttribute('_title', $this->getContext()->getTranslationManager()->_('Login', 'user.ui'));
    }

    /**
     * Prepares and sets our json data on our webresponse.
     *
     * @param AgaviRequestDataHolder $request_data
     */
    public function executeJson(AgaviRequestDataHolder $request_data)
    {
        $json = json_encode(
            array(
                'result'  => 'success',
                'message' => $this->getContext()->getTranslationManager()->_('You may post a username and a password to this uri in order to login to the application.')
            )
        );

        $this->getContainer()->getResponse()->setContent($json);
        $this->getContext()->getController()->getGlobalResponse()->setHttpStatusCode(401);
    }

    public function executeBinary(AgaviRequestDataHolder $request_data) // @codingStandardsIgnoreEnd
    {
        $this->getContext()->getController()->getGlobalResponse()->setHttpStatusCode(401);
    }

    /**
     * @param AgaviRequestDataHolder $request_data
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeConsole(AgaviRequestDataHolder $request_data) // @codingStandardsIgnoreEnd
    {
        $this->getContainer()->getResponse()->setContent(
            $this->getContext()->getTranslationManager()->_(
                'Please provide username and password as commandline arguments when calling secure actions. ' .
                'Use --username {user} --password {pass}.',
                'user.messages'
            ) . PHP_EOL
        );

        $this->getResponse()->setExitCode(1);
    }
}
