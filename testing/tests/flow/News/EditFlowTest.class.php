<?php

class EditFlowTest extends AgaviFlowTestCase
{
    protected $ticketData;

    protected $supervisor;

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
            'elasticsearch' => 'News.ReadFixtures',
            'couchdb' => 'News.WriteFixtures'
        ));
        $this->supervisor = WorkflowSupervisorFactory::createByTypeKey('news');
        $couchDb = $this->supervisor->getDatabase();
        $ticketResp = $couchDb->getView(NULL, 'tickets', 'all', array('limit' => 1));

        $ticketData = $ticketResp['rows'][0];

        $this->ticketData = array(
            'id' => $ticketData['id'],
            'item' => $ticketData['value']['_id']
        );
    }

    public function testExecuteRead()
    {
        $this->login();
        $this->dispatch(array('ticket' => $this->ticketData['id'], 'type' => 'news'));
        $this->assertResponseHasTag(array(
            'tag'      => 'menu',
            'attributes' => array('id' => 'content-item-menu')
        ));
        $this->assertResponseHasTag(array(
            'tag'      => 'menu',
            'attributes' => array('id' => 'import-item-menu')
        ));
    }

    public function testExecuteWriteError()
    {
        $this->login();

        $ticketPeer = $this->supervisor->getWorkflowTicketStore();
        $ticket = $ticketPeer->fetchByIdentifier($this->ticketData['id']);
        $ticket->setCurrentOwner('general');
        $ticketPeer->save($ticket);

        $contentItemData = json_decode($this->getContentItemJsonFixture(), TRUE);
        $contentItemData['identifier'] = $this->ticketData['item'] . '-1';
        $contentItemData['parentIdentifier'] = $this->ticketData['item'];
        unset($contentItemData['title']);
        $arguments = array(
            'ticket' => $this->ticketData['id'],
            'content_item' => $contentItemData,
            'type' => 'news'
        );
        $this->dispatch($arguments, 'json', 'write');
        $response = json_decode($this->response->getContent(), TRUE);
        $this->assertArrayHasKey('msg', $response);
        $this->assertArrayHasKey('state', $response);
        $this->assertEquals('error', $response['state']);
    }

    public function testExecuteWrite()
    {
        $this->login();

        $ticketPeer =$this->supervisor->getWorkflowTicketStore();
        $ticket = $ticketPeer->fetchByIdentifier($this->ticketData['id']);
        $ticket->setCurrentOwner('general');
        $ticketPeer->save($ticket);

        $contentItemData = json_decode($this->getContentItemJsonFixture(), TRUE);
        $contentItemData['identifier'] = $this->ticketData['item'] . '-1';
        $contentItemData['parentIdentifier'] = $this->ticketData['item'];
        $arguments = array(
            'ticket' => $this->ticketData['id'],
            'content_item' => $contentItemData,
            'type' => 'news'
        );
        $this->dispatch($arguments, 'json', 'write');
        $response = json_decode($this->response->getContent(), TRUE);
        $this->assertArrayHasKey('state', $response);
        $this->assertEquals('ok', $response['state']);
    }

    public function testPublish()
    {
        $this->contextName = 'web';
		$this->actionName = 'Proceed';
		$this->moduleName = 'Workflow';
		$this->input = '/de/workflow/proceed';
        $this->login();
        $this->dispatch(array('ticket' => $this->ticketData['id'], 'gate' => 'publish', 'type' => 'news'), 'json', 'write');

        $response = json_decode($this->response->getContent(), TRUE);
        $this->assertArrayHasKey('state', $response);
        $this->assertEquals('ok', $response['state']);
    }

    public function testDelete()
    {
        $this->contextName = 'web';
		$this->actionName = 'Proceed';
		$this->moduleName = 'Workflow';
		$this->input = '/de/workflow/proceed';
        $this->login();
        $this->dispatch(array('ticket' => $this->ticketData['id'], 'gate' => 'delete', 'type' => 'news'), 'json', 'write');

        $response = json_decode($this->response->getContent(), TRUE);
        $this->assertArrayHasKey('state', $response);
        $this->assertEquals('ok', $response['state']);
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

    protected function getContentItemJsonFixture()
    {
        return <<<FIXTURE
{
    "title": "Büro-Knigge: Was den Chef in Rage bringt",
    "priority": 1,
    "category": "polizeimeldungen",
    "tags": [
        "geschlossen"
    ],
    "teaser": "Auch im kleinsten Fettnapf ist noch Platz: Gerade im Umgang mit dem Chef kann Unbedacht katastrophale Folgen haben.",
    "text": "Auch im kleinsten Fettnapf ist noch Platz: Gerade im Umgang mit dem Chef kann Unbedacht katastrophale Folgen haben. Wer den Vorgesetzten vor Kollegen bloßstellt oder penetrant schleimt, manövriert sich ins Abseits. Ein paar Regeln bewahren vor dem Schlimmsten.",
    "date": {
        "from": "12.02.2012",
        "till": "22.02.2012"
    },
    "location": {
        "coordinates": {
            "lat": 52.5016746,
            "lon": 13.3401646
        },
        "city": null,
        "postalCode": "10789",
        "administrativeDistrict": "Tempelhof-Schöneberg",
        "district": "Schöneberg",
        "neighborhood": "Schöneberg",
        "street": "Passauer Str. 1-3",
        "housenumber": null,
        "name": "kadewe",
        "relevance": 0
    },
    "source": "spon",
    "publisher": "general"
}
FIXTURE;
    }
}

?>
