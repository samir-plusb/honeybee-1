<?php

/**
 * @agaviIsolationDefaultContext console
 */
class ImperiaActionTest extends AgaviActionTestCase
{
    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        
        $this->contextName = 'console';
        $this->moduleName = 'Import';
        $this->actionName = 'Imperia';
    }

    public function testPositiveImport()
    {
        $this->runActionWithParameters('write', array('c' => 'polizeimeldungen'));
        $this->assertValidatedArgument('c');
        $this->assertViewNameEquals('Success');
    }

    public function testMissingParameter()
    {
        $this->runActionWithParameters('write', array());
        $this->assertViewNameEquals('Error');
    }

    public function testFalseParameter()
    {
        $this->runActionWithParameters('write', array('c' => 'foobar'));
        $this->assertViewNameEquals('Error');
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
                    AgaviWebRequestDataHolder::SOURCE_PARAMETERS => $arguments
                )
            )
        );
        
        $this->runAction();
    }
}

?>