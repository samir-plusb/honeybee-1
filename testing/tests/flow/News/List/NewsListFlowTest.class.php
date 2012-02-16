<?php

/**
 * @agaviRoutingInput de/items/list
 * @agaviRequestMethod Read
 * @agaviIsolationDefaultContext web
 */
class NewsListFlowTest extends AgaviFlowTestCase
{

    public function testListWithoutParams()
    {
        $this->login();
        $this->dispatch();
        $matcher = array(
            'tag' => 'table'
        );
        $this->assertResponseHasTag($matcher, 'Missing data table on page.');
    }

    protected function login()
    {
        $user = $this->getContext()->getUser();
        $username = "general_g";
        $password = "n0tf0und";
        $authProviderClass = AgaviConfig::get('core.auth_provider');

        $authProvider = new $authProviderClass();
        $authResponse = $authProvider->authenticate($username, $password);
        $user->setAttributes($authResponse->getAttributes());
        $user->setAuthenticated(AuthResponse::STATE_AUTHORIZED === $authResponse->getState());
    }
}

?>
