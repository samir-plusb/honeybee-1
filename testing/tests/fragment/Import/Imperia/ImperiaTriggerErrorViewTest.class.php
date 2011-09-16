<?php

/**
 * @agaviIsolationDefaultContext web
 */
class ImperiaTriggerErrorViewTest extends AgaviViewTestCase
{
    // As these are run outside of the code coverage's scope, they allways will be marked as non-executed.
    // @codeCoverageIgnoreStart

    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->contextName = 'web';
        $this->moduleName = 'Import';
        $this->actionName = 'TriggerImperia';
        $this->viewName = 'Error';
    }

    // @codeCoverageIgnoreEnd

    public function testHandlesTextOutputType()
	{
		$this->assertHandlesOutputType('json');
	}

    public function testTextResponse()
    {
        $this->runView('json');
        $this->assertViewResponseContainsContent('{"ok":false,"errors":[]}');
    }

    protected function assertViewResponseContainsContent($expected, $message = 'Failed asserting that the response contains content <%1$s>.')
	{
		$response = $this->container->getResponse();
        $this->assertContains($expected, $response->getContent(), sprintf($message, $expected), TRUE);
	}
}

?>