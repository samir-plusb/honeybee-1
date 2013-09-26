<?php

/**
 * View executed after successful authentication attempts.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @package         User
 */
class User_Login_LoginSuccessView extends UserBaseView
{
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
        $default_target_url = $this->routing->gen('index');  // dashboard a.k.a. homepage

        // login after input view - redirect to previous original target or referring URL
        if ($this->user->hasAttribute('redirect_url', 'de.honeybee-cms.login')) {
            $url = $this->user->removeAttribute('redirect_url', 'de.honeybee-cms.login', $default_target_url);
            $this->setRedirect($url);
            return;
        }

        // login via internal forward - forward back to originally requested action
        if ($this->container->hasAttributeNamespace('org.agavi.controller.forwards.login')) {
            $container = null;
            $agavi_login_namespace = 'org.agavi.controller.forwards.login';
            $requested_module = $this->container->getAttribute('requested_module', $agavi_login_namespace);
            $requested_action = $this->container->getAttribute('requested_action', $agavi_login_namespace);
            if (!empty($requested_module) && !empty($requested_action)) {
                $container = $this->createForwardContainer($requested_module, $requested_action);
            }

            if (null !== $container) {
                return $container;
            }
        }

        // normal login via login form - no success template, but direct redirect to dashboard
        $this->setRedirect($default_target_url);
    }

    /**
     * IE forwards the non-GET methods when using HTTP status code 302. That's why we use
     * "303 See Other" if possible (as GET is the default method to use for those redirects).
     *
     * @param string $url redirect target URL
     */
    protected function setRedirect($url)
    {
        if ($this->request->getProtocol() === 'HTTP/1.1') {
            $this->getResponse()->setRedirect($url, 303);
        } else {
            $this->getResponse()->setRedirect($url, 302);
        }
    }
}
