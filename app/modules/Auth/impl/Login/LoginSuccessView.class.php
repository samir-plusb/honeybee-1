<?php

class Auth_Login_LoginSuccessView extends AuthBaseView
{
    /**
     * Execute any presentation logic and set template attributes.
     */
    public function executeHtml(AgaviRequestDataHolder $rd)
    {
        if (null != ($container = $this->attemptForward($rd)))
        {
            return $container;
        }

        $user = $this->getContext()->getUser();

        if ($user->hasAttribute('redirect', 'de.berlinonline.contentworker.login'))
        {
            $target = $user->removeAttribute('redirect', 'de.berlinonline.contentworker.login');
        }
        else
        {
            $target = $this->getContext()->getRouting()->gen('index');
        }

        $this->getResponse()->setRedirect($target);
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
                    'result' => 'success',
                    'token' => session_id()
                )
            )
        );
    }

    public function executeText(AgaviRequestDataHolder $rd)
    {
        if (null != ($container = $this->attemptForward($rd)))
        {
            return $container;
        }

        $this->getContainer()->getResponse()->setContent(
            'The authentication completed successfully. The session token is: ' . "\n" . session_id() . "\n"
        );
    }

    protected function attemptForward(AgaviRequestDataHolder $rd)
    {
        $rq = $this->getContext()->getRequest();
        $requested_module = $rq->getAttribute('requested_module', 'org.agavi.controller.forwards.login');
        $requested_action = $rq->getAttribute('requested_action', 'org.agavi.controller.forwards.login');

        $container = null;
        
        if (!empty($requested_module) && !empty($requested_action))
        {
            $container = $this->createForwardContainer($requested_module, $requested_action);
        }

        return $container;
    }

}

?>
