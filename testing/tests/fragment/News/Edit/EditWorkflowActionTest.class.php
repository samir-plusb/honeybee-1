<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Description of EditWorkflowActionTest
 *
 * @author shrink
 */
class EditWorkflowActionTest extends AgaviActionTestCase
{
    // As these are run outside of the code coverage's scope, they allways will be marked as non-executed.
    // @codeCoverageIgnoreStart

    protected $ticketId;

    protected $supervisor;

    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->contextName = 'web';
        $this->moduleName = 'Workflow';
        $this->actionName = 'Run';
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
        $this->ticketId = $ticketResp['rows'][0]['id'];
        $this->login();
    }

    // @codeCoverageIgnoreEnd

    public function testExecuteRead()
    {
        $this->runActionWithParameters('read', array('ticket' => $this->ticketId, 'type' => 'news'));
        $this->assertViewNameEquals('Success');
        $this->assertContainerAttributeExists('content');

        // @todo validate the content (edit input template)
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
