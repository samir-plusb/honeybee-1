<?php

class EditFlowTest extends AgaviFlowTestCase
{
    public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);

        $this->contextName = 'web';
		$this->actionName = 'Run';
		$this->moduleName = 'Workflow';
		$this->input = '/de/workflow/run';
	}

    public function setUp()
    {
        parent::setUp();

        AgaviConfig::set('news.connections', array(
            'elasticsearch' => 'EsNewsFixtures',
            'couchdb' => 'CouchWorkflowFixtures'
        ));
        $couchDb = Workflow_SupervisorModel::getInstance()->getDatabase();
        $ticketResp = $couchDb->getView(NULL, 'designWorkflow', 'ticketList', array('limit' => 1));
        $this->ticketId = $ticketResp['rows'][0]['id'];
        $this->login();
    }

    public function testExecuteRead()
    {
        $this->login();
        $this->dispatch(array('ticket' => $this->ticketId));

        $this->assertResponseHasTag(array(
            'tag'      => 'menu',
            'attributes' => array('id' => 'content-item-menu')
        ));
        $this->assertResponseHasTag(array(
            'tag'      => 'menu',
            'attributes' => array('id' => 'import-item-menu')
        ));
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
