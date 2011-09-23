<?php

/**
 * @agaviIsolationDefaultContext console
 */
class MailTriggerErrorViewTest extends AgaviViewTestCase
{
    // As these are run outside of the code coverage's scope, they allways will be marked as non-executed.
    // @codeCoverageIgnoreStart

    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->contextName = 'console';
        $this->moduleName = 'Import';
        $this->actionName = 'TriggerMail';
        $this->viewName = 'Error';
    }

    // @codeCoverageIgnoreEnd

    public function testHandlesTextOutputType()
	{
		$this->assertHandlesOutputType('text');
	}

    public function testTextResponse()
    {
        $this->runView('text');
        $this->assertViewResponseContainsContent('An arror occured while trying to process your mail:');
    }

    protected function assertViewResponseContainsContent($expected, $message = 'Failed asserting that the response contains content <%1$s>.')
	{
		$response = $this->container->getResponse();
        $this->assertContains($expected, $response->getContent(), sprintf($message, $expected), TRUE);
	}
}

?>