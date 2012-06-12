<?php

/**
 * @agaviIsolationDefaultContext web
 */
class Import_ImperiaSuccessViewTest extends AgaviViewTestCase
{
    // As these are run outside of the code coverage's scope, they allways will be marked as non-executed.
    // @codeCoverageIgnoreStart

    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->contextName = 'web';
        $this->moduleName = 'News';
        $this->actionName = 'Import_Imperia';
        $this->viewName = 'Success';
    }

    // @codeCoverageIgnoreEnd

    public function testHandlesTextOutputType()
	{
		$this->assertHandlesOutputType('json');
	}

    public function testTextResponse()
    {
        $this->runView('json');
        $this->assertViewResponseHasContent('{"ok":true}');
    }
}

?>