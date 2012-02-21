<?php

class StatsFlowTest extends AgaviFlowTestCase
{
    public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);

        $this->contextName = 'web';
		$this->actionName = 'Stats';
		$this->moduleName = 'News';
		$this->input = '/de/news/stats';
	}

    public function setUp()
    {
        AgaviConfig::set('news.connections', array(
            'elasticsearch' => 'EsNewsFixtures',
            'couchdb' => 'CouchWorkflowFixtures',
        ));
    }

    public function testDefaultListWithoutParameters()
    {
        $this->login();
        $this->dispatch();

        $this->assertResponseHasTag(array(
            'tag'      => 'ul',
            'children' => array(
                'count' => 23,
                'only' => array('tag' => 'li')
            )
        ), 'The Stats list should contain 30 news list entries as rows for current fixtures.');
    }

    // the http redirects set by the login view make it hard to test transparently against secure actions atm.
    // so we fake the login by directly calling the auth provider with a static testing-only account.
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
