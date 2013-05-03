<?php

/**
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @package         User
 */
class User_Login_LoginSuccessView extends UserBaseView
{
    public function executeBinary(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $this->executeHtml($parameters);
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
        $user = $this->getContext()->getUser();
        $target = $this->getContext()->getRouting()->gen('index');

        if ($user->hasAttribute('redirect', 'de.org.honeybee.user.login'))
        {
            $target = $user->removeAttribute('redirect', 'de.org.honeybee.user.login');
        }

        $this->getResponse()->setRedirect($target);
    }

    /**
     * Prepares and sets our json data on our webresponse.
     *
     * @param       AgaviRequestDataHolder $parameters
     */
    public function executeJson(AgaviRequestDataHolder $parameters)
    {
        if (NULL !== ($container = $this->attemptForward($parameters)))
        {
            return $container;
        }

        $jsonData = json_encode(array('result' => 'success'));
        $this->getContainer()->getResponse()->setContent($jsonData);
    }

    /**
     * Prepares and sets our json data on our console response.
     *
     * @param       AgaviRequestDataHolder $parameters
     */
    public function executeText(AgaviRequestDataHolder $parameters)
    {
        if (NULL !== ($container = $this->attemptForward($parameters)))
        {
            return $container;
        }

        $this->getContainer()->getResponse()->setContent('The userentication completed successfully.');
    }

    /**
     * Create a forward container for the that was intentionally called before the login was executed.
     *
     * @return      AgaviExecutionContainer A new execution container instance,
	 *                                      fully initialized.
	 *
	 * @see         AgaviExecutionContainer::createExecutionContainer()
     */
    protected function attemptForward()
    {
        $request = $this->getContext()->getRequest();
        $requestedModule = $request->getAttribute('requested_module', 'org.agavi.controller.forwards.login');
        $requestedAction = $request->getAttribute('requested_action', 'org.agavi.controller.forwards.login');

        $container = NULL;

        if (!empty($requestedModule) && !empty($requestedAction))
        {
            $container = $this->createForwardContainer($requestedModule, $requestedAction, NULL, NULL, 'read');
        }

        return $container;
    }

}
