<?php

/**
 * @agaviIsolationDefaultContext console
 */
class ImperiaSuccessViewTest extends AgaviViewTestCase
{
    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        
        $this->contextName = 'console';
        $this->moduleName = 'Import';
        $this->actionName = 'Imperia';
        $this->viewName = 'Success';
    }

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