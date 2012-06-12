<?php

/**
 * @agaviIsolationDefaultContext web
 */
class Import_ImperiaActionTest extends AgaviActionTestCase
{
    // As these are run outside of the code coverage's scope, they allways will be marked as non-executed.
    // @codeCoverageIgnoreStart

    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->contextName = 'web';
        $this->moduleName = 'News';
        $this->actionName = 'Import_Imperia';
    }

    // @codeCoverageIgnoreEnd

    public function testRunImportMissingParam()
    {
        $this->runActionWithParameters('write', array());
        $this->assertViewNameEquals('Error');
    }

    public function testRunImportInvalidParam()
    {
        $this->runActionWithParameters('write', array('data' => 'foobar = 1'));
        $this->assertViewNameEquals('Error');
    }

    /**
     *
     * @dataProvider getFixtureData
     */
    public function testRunImportSingleSuccess()
    {
        $data = $this->getFixtureData();
        $data = array($data[0]);

        $this->runActionWithParameters('write', array('data' => json_encode($data)));
        $this->assertValidatedArgument('data');
        $this->assertViewNameEquals('Success');
    }

    /**
     *
     * @param       string $importName
     * @param       string $datasourceNames
     *
     */
    public function testRunImportMultipleSuccess()
    {
        $data = $this->getFixtureData();
        $this->runActionWithParameters('write', array('data' => json_encode($data)));
        $this->assertValidatedArgument('data');
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

    public function getFixtureData()
    {
        $nodes = array(
            '/2/10330/10343/10890/1387691' => '/polizei/presse-fahndung/archiv/358951/index.html',
            '/2/10/11536/11678/1387190' => '/ba-charlottenburg-wilmersdorf/presse/archiv/20110913.0940.358475.html',
        );

        $set = array();
        foreach ($nodes as $node => $url)
        {
            $set[] = array(
                'uri' => $url,
                '__imperia_node_id' => $node,
                '__imperia_modified' => time(),
                'publish_date' => time(),
                'expiry_date' =>  time() + 86400
            );
        }
        return $set;
    }
}

?>