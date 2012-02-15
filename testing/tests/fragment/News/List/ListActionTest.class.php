<?php

class ListActionTest extends AgaviActionTestCase
{
    // As these are run outside of the code coverage's scope, they allways will be marked as non-executed.
    // @codeCoverageIgnoreStart

    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->contextName = 'web';
        $this->moduleName = 'News';
        $this->actionName = 'List';

        $midasSetup = new MidasIndexSetup(
            $this->getContext()->getDatabaseManager()->getDatabase('EsNews')
        );
        $midasSetup->setup(TRUE);
    }

    // @codeCoverageIgnoreEnd

    public function testReadDefaultList()
    {
        $this->runActionWithParameters('read', array());
        $this->assertViewNameEquals('Success');
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
                    AgaviConsoleRequestDataHolder::SOURCE_FILES => $arguments
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
