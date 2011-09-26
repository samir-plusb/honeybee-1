<?php

/**
 * @agaviIsolationDefaultContext console
 */
class RunActionTest extends AgaviActionTestCase
{
    // As these are run outside of the code coverage's scope, they allways will be marked as non-executed.
    // @codeCoverageIgnoreStart

    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->contextName = 'console';
        $this->moduleName = 'Import';
        $this->actionName = 'Run';
    }

    // @codeCoverageIgnoreEnd

    public function testRunImportMissingParam()
    {
        $this->runActionWithParameters('write', array());
        $this->assertViewNameEquals('Error');
    }

    public function testRunImportInvalidParam()
    {
        $this->runActionWithParameters('write', array('i' => 'foobar'));
        $this->assertViewNameEquals('Error');
    }

    /**
     *
     * @param       string $importName
     * @param       string $datasourceNames
     *
     * @dataProvider provideTestRunImportArgs
     */
    public function testRunImport($importName, $dataSourceNames)
    {
        $this->runActionWithParameters('write', array('i' => $importName, 'd' => $dataSourceNames));
        $this->assertValidatedArgument('data_import');
        $this->assertValidatedArgument('data_sources');
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
                    AgaviWebRequestDataHolder::SOURCE_PARAMETERS => $arguments
                )
            )
        );

        $this->runAction();
    }

    // @codeCoverageIgnoreStart

    public function provideTestRunImportArgs()
    {
        return array(
            array( // Test the Import/Imperia Package
                'importName'      => 'couchdb',
                'dataSourceNames' => 'imperia'
            ),
            array( // Test the Import/Newsiwire Package
                'importName'      => 'couchdb',
                'dataSourceNames' => 'dpa'
            )
        );
    }

    // @codeCoverageIgnoreEnd
}

?>