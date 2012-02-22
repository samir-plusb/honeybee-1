<?php

class ListFlowTest extends AgaviFlowTestCase
{
    public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);

        $this->contextName = 'web';
		$this->actionName = 'List';
		$this->moduleName = 'News';
		$this->input = '/de/news/list';
	}

    public function setUp()
    {
        AgaviConfig::set('news.connections', array(
            'elasticsearch' => 'EsNewsFixtures',
            'couchdb' => 'CouchWorkflowFixtures',
        ));
    }

    public function testDefaultListWithoutParametersHtml()
    {
        $this->login();
        $this->dispatch();

        $this->assertResponseHasTag(array(
            'tag'      => 'tbody',
            'children' => array(
                'count' => 30,
                'only' => array('tag' => 'tr')
            )
        ), 'News list table body should contain 30 news list entries as rows for current fixtures.');
    }

    public function testDefaultListWithoutParameterJson()
    {
        $this->login();
        $this->dispatch(array(), 'json');

        $resp = $this->response->getContent();
        $data = json_decode($resp, TRUE);

        $this->assertArrayHasKey('state', $data);
        $this->assertEquals('ok', $data['state']);
        $this->assertEquals(30, count($data['data']));
    }

    public function testDefaultListHasCorrectNumberOfResults()
    {
        $this->login();
        $this->dispatch();

        $this->assertResponseHasNotTag(array(
            'tag'      => 'tbody',
            'children' => array(
                'greater_than' => 30,
                'less_than' => 30,
                'only' => array('tag' => 'tr')
            )
        ), 'Fixtures should contain exactly 30 news entry rows in the table body of the list view results.');
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
