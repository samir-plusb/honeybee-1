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

        if ($this->container->hasAttributeNamespace('org.agavi.controller.forwards.login')) {
            // forward from controller due to secure action (and login action could not authenticate automatically)
            // store the input URL in the session for a redirect after login
            $base_href = $this->routing->getBaseHref();
            $url = $this->request->getUrl();
            // we only want to store the requested URL when it starts with the current base href
            if (strpos($url, "$base_href", 0) === 0) {
                // only store URL when it was a GET as otherwise the URL may not even have a read method
                if ($this->request->getMethod() !== 'read') {
                    // we store the REFERER when the request is not GET, as it's most probably a form on that page.
                    // when no valid REFERER is available we use the target input URL instead
                    $url = $request_data->get('headers', 'REFERER', $url);
                }
                $this->user->setAttribute('redirect_url', $url, 'de.honeybee-cms.login');
            }
            // as this is an internal forward the input form will not be the expected output of users/consumers,
            // thus we need to tell them that they're not authenticated and must fill the form or fix it otherwise
            $this->getResponse()->setHttpStatusCode(401);
        } else {
            // clear redirect from session as it's probably just a direct request of this login form
            if ($this->request->getMethod() === 'read') {
                $this->user->removeAttribute('redirect_url', 'de.honeybee-cms.login');
            } else {
                // when users submit wrong credentials we don't want to forget his original target url
            }
        }
    }

    /**
     * Prepares and sets our json data on our webresponse.
     *
     * @param AgaviRequestDataHolder $request_data
     */
    public function executeJson(AgaviRequestDataHolder $request_data)
    {
        return json_encode(
            array(
                'result'  => 'success',
                'message' => $this->translation_manager->_(
                    'You may post a username and a password to this uri in order to login to the application.',
                    'user.messages'
                )
            )
        );
    }

    /**
     * @param AgaviRequestDataHolder $request_data
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeConsole(AgaviRequestDataHolder $request_data) // @codingStandardsIgnoreEnd
    {
        $error_message = $this->translation_manager->_(
            'Please provide username and password as commandline arguments when calling secure actions. ' .
            'Use -username {user} -password {pass}.',
            'user.messages'
        ) . PHP_EOL;

        return $this->cliError($error_message);
    }

    /**
     * Handles non-existing methods. This includes mainly the not implemented
     * handling of certain output types. This returns HTTP status code 406 by default.
     *
     * @param string $method_name
     * @param array $arguments
     */
    public function __call($method_name, $arguments)
    {
        // TODO wouldn't it be nice if agavi just throws 406 every time an output type is not supported?
        if (preg_match('~^(execute)([A-Za-z_]+)$~', $method_name)) {
            if ($this->getResponse() instanceof AgaviWebResponse) {
                $this->getResponse()->setHttpStatusCode(406); // Not Acceptable
            } elseif ($this->getResponse() instanceof AgaviConsoleResponse) {
                $this->getResponse()->setExitCode(70); // 70 ("internal software error") instead of 1 ("general error")
            }
        }
    }
}
