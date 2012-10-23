<?php

/**
 * @agaviIsolationDefaultContext web
 */
class Import_PrototypeDeleteActionTest extends AgaviActionTestCase
{
    protected $shofiPlace;

    protected $shofiCategory;

    // As these are run outside of the code coverage's scope, they allways will be marked as non-executed.
    // @codeCoverageIgnoreStart

    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->contextName = 'web';
        $this->moduleName = 'Shofi';
        $this->actionName = 'Import_PrototypeDelete';
    }

    public function setUp()
    {
        parent::setUp();

        // teardown and setup the couch
        $setup = new ShofiDatabaseSetup();
        $setup->setup(TRUE);

        // place fixture
        $placeFix = AgaviConfig::get('core.fixtures_dir') . 'Shofi/shofi.item.json';
        $placeData = json_decode(file_get_contents($placeFix), TRUE);
        $this->shofiPlace = ShofiWorkflowItem::fromArray($placeData);
        ShofiWorkflowService::getInstance()->storeWorkflowItem($this->shofiPlace);

        // category fixture
        $categoryFix = AgaviConfig::get('core.fixtures_dir') . 'Shofi_Categories/shofi.category.json';
        $categoryData = json_decode(file_get_contents($categoryFix), TRUE);
        $this->shofiCategory = ShofiCategoriesWorkflowItem::fromArray($categoryData);
        ShofiCategoriesWorkflowService::getInstance()->storeWorkflowItem($this->shofiCategory);
    }

    // @codeCoverageIgnoreEnd

    public function testRunImportMissingParam()
    {
        $this->runActionWithParameters('write', array('id' => '1234'));
        $this->assertViewNameEquals('Error');
    }

    public function testRunImportInvalidParam()
    {
        $this->runActionWithParameters('write', array('id' => '1234', 'type' => 'moo'));
        $this->assertViewNameEquals('Error');
    }

    public function testRunDeletePlaceSuccess()
    {
        $this->runActionWithParameters('write', array('id' => '4f6724a6be8077692d00000a', 'type' => 'place'));
        $this->assertViewNameEquals('Success');
    }

    public function testRunDeleteCategorySuccess()
    {
        $this->runActionWithParameters('write', array('id' => '4f1439bfb4fc475f0b000000', 'type' => 'category'));
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
}

?>