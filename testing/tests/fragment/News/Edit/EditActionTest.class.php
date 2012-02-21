<?php

class EditActionTest extends AgaviActionTestCase
{
    // As these are run outside of the code coverage's scope, they allways will be marked as non-executed.
    // @codeCoverageIgnoreStart

    protected $ticketId;

    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->contextName = 'web';
        $this->moduleName = 'News';
        $this->actionName = 'Edit';
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
    }

    // @codeCoverageIgnoreEnd

    public function testDefaultRead()
    {
        $this->runActionWithParameters('read', array('ticket' => $this->ticketId));
        $this->assertViewNameEquals('Input');
        $this->assertContainerAttributeExists('ticket');
        $ticket = $this->container->getAttribute('ticket');
        $this->assertEquals($this->ticketId, $ticket->getIdentifier());
    }

    /**
     * run this action
     *
     * @param string $method request method like 'write', 'read'
     * @param array $arguments for the action
     */
    protected function runActionWithParameters($method, array $arguments)
    {
        $this->setRequestMethod($method);
        $this->setArguments(
            $this->createRequestDataHolder(
                array(
                    AgaviConsoleRequestDataHolder::SOURCE_PARAMETERS => $arguments
                )
            )
        );
        $this->runAction();
    }
}

?>
