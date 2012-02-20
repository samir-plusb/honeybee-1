<?php

class ListActionTest extends AgaviActionTestCase
{
    // As these are run outside of the code coverage's scope, they allways will be marked as non-executed.
    // @codeCoverageIgnoreStart

    public static function setupBeforeClass($name = NULL, array $data = array(), $dataName = '')
    {
        parent::setUpBeforeClass($name, $data, $dataName);

        $midasSetup = new MidasIndexSetup();
        $midasSetup->tearDown();

        $workflowSetup = new WorkflowDatabaseSetup();
        $workflowSetup->setup(TRUE);

        $command = sprintf(
            "AGAVI_ENVIRONMENT=%s %s/bin/cli import.run -i workflow -d rss",
            ProjectEnvironmentConfig::getEnvironment(),
            dirname(AgaviConfig::get('core.app_dir'))
        );
        $output = array();
        exec($command, $output);

        $midasSetup->setUp();
    }

    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        $this->setRunTestInSeparateProcess(FALSE);
        parent::__construct($name, $data, $dataName);

        $this->contextName = 'web';
        $this->moduleName = 'News';
        $this->actionName = 'List';
    }

    // @codeCoverageIgnoreEnd

    public function testReadDefaultList()
    {
        $this->runActionWithParameters('read', array());
        $this->assertViewNameEquals('Success');
        $this->assertContainerAttributeExists('items');

        $itemCount = count($this->container->getAttribute('items'));
        error_log("testReadDefaultList --> ITEEEM COUNT: " . $itemCount);
        $expectedItems = 20;
        $this->assertEquals($expectedItems, $itemCount);
    }

    public function testReadListWithLimitAndOfsset()
    {
        $this->runActionWithParameters('read', array('limit' => 10, 'offset' => 3));
        $this->assertViewNameEquals('Success');
        $this->assertContainerAttributeExists('items');

        $itemCount = count($this->container->getAttribute('items'));
        error_log("testReadListWithLimitAndOfsset --> ITEEEM COUNT: " . $itemCount);
        $expectedItems = 20;
        $this->assertEquals($expectedItems, $itemCount);
    }

    public function testSearchDefaultList()
    {
        $this->runActionWithParameters('read', array('search_term' => 'das*'));
        $this->assertViewNameEquals('Success');
        $this->assertContainerAttributeExists('items');

        $itemCount = count($this->container->getAttribute('items'));
        error_log("testSearchDefaultList --> ITEEEM COUNT: " . $itemCount);
        $expectedItems = 20;
        $this->assertEquals($expectedItems, $itemCount);
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

    protected function getTemplateFile()
	{
		if($this->doBootstrap())
        {
			return AgaviConfig::get('core.testing_dir') . DIRECTORY_SEPARATOR . 'TestCaseMethod.tpl';
		}

		return null;
	}
}

?>
