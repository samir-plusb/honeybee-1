<?php

/**
 * @agaviIsolationDefaultContext web
 */
class Import_PrototypeCategoriesActionTest extends AgaviActionTestCase
{
    const JSON_FIXTURE_PATH = 'Shofi_Categories/import/prototype/fixture.category.json';

    // As these are run outside of the code coverage's scope, they allways will be marked as non-executed.
    // @codeCoverageIgnoreStart

    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->contextName = 'web';
        $this->moduleName = 'Shofi_Categories';
        $this->actionName = 'Import_Prototype';
        $this->viewName = 'Error';
    }

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $dbSetup = new ShofiDatabaseSetup();
        $dbSetup->setup(TRUE);
    }

    // @codeCoverageIgnoreEnd

    public function testRunImportMissingParam()
    {
        $this->runActionWithParameters('write', array());
        $this->assertViewNameEquals('Error');
    }

    public function testRunImportInvalidParam()
    {
        $this->runActionWithParameters('write', array(
            'asdasdas' => array('foobar' => 'moo')
         ));

        $this->assertViewNameEquals('Error');
    }

    public function testRunImportSingleSuccess()
    {
        $this->runActionWithParameters('write', $this->getFixtureData());
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
                    AgaviConsoleRequestDataHolder::SOURCE_PARAMETERS => $arguments
                )
            )
        );
        $this->runAction();
    }

    public function getFixtureData()
    {
        $path = AgaviConfig::get('core.fixtures_dir') . self::JSON_FIXTURE_PATH;
        $json = file_get_contents($path);
        return json_decode($json, TRUE);
    }
}

?>
