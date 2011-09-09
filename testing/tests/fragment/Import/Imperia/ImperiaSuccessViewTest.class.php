<?php

/**
 * @agaviIsolationDefaultContext console
 */
class ImperiaSuccessViewTest extends AgaviViewTestCase
{
    // As these are run outside of the code coverage's scope, they allways will be marked as non-executed.
    // @codeCoverageIgnoreStart
    
    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        
        $this->contextName = 'console';
        $this->moduleName = 'Import';
        $this->actionName = 'Imperia';
        $this->viewName = 'Success';
    }
    
    // @codeCoverageIgnoreEnd

    public function testHandlesTextOutputType()
	{
		$this->assertHandlesOutputType('text');
	}

    public function testTextResponse()
    {
        $this->runView();
        $this->assertViewResponseHasContent('Import succeeded.');
    }
}

?>