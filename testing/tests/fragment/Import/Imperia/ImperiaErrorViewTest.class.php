<?php

/**
 * @agaviIsolationDefaultContext console
 */
class ImperiaErrorViewTest extends AgaviViewTestCase
{
    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        
        $this->contextName = 'console';
        $this->moduleName = 'Import';
        $this->actionName = 'Imperia';
        $this->viewName = 'Error';
    }

    public function testHandlesTextOutputType()
	{
		$this->assertHandlesOutputType('text');
	}

    public function testTextResponse()
    {
        $this->runView();
        $this->assertViewResponseContainsContent('An error occoured:');
    }
    
    protected function assertViewResponseContainsContent($expected, $message = 'Failed asserting that the response contains content <%1$s>.')
	{
		$response = $this->container->getResponse();
        $this->assertContains($expected, $response->getContent(), sprintf($message, $expected), TRUE);
	}
}

?>