<?php

class StatsActionTest extends AgaviActionTestCase
{
    // As these are run outside of the code coverage's scope, they allways will be marked as non-executed.
    // @codeCoverageIgnoreStart

    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->contextName = 'web';
        $this->moduleName = 'News';
        $this->actionName = 'Stats';
    }

    public function setUp()
    {
        parent::setUp();

        AgaviConfig::set('news.connections', array(
            'elasticsearch' => 'News.ReadFixtures',
            'couchdb' => 'News.WriteFixtures'
        ));
    }

    // @codeCoverageIgnoreEnd

    public function testDefaultRead()
    {
        $this->runActionWithParameters('read', array());
        $this->assertViewNameEquals('Success');
        $this->assertContainerAttributeExists('statistics');

        $itemCount = count($this->container->getAttribute('statistics'));
        $expectedItems = count(NewsStatisticProvider::getDistricts());
        $this->assertEquals($expectedItems, $itemCount);
    }

    public function testReadDistrict()
    {
        $this->runActionWithParameters('read', array('district' => NewsStatisticProvider::DISTRICT_CHAR));
        $this->assertViewNameEquals('Success');
        $this->assertContainerAttributeExists('statistics');

        $itemCount = count($this->container->getAttribute('statistics'));
        $expectedItems = 1;
        $this->assertEquals($expectedItems, $itemCount);
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
