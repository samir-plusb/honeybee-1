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

    public function testReadDefaultList()
    {
        $this->runActionWithParameters('read', array());
        $this->assertViewNameEquals('Success');
        $this->assertContainerAttributeExists('items');

        $itemCount = count($this->container->getAttribute('items'));
        $expectedItems = 30;
        $this->assertEquals($expectedItems, $itemCount);
    }

    public function testReadListWithLimitAndOfsset()
    {
        $this->runActionWithParameters('read', array('limit' => 10, 'offset' => 3));
        $this->assertViewNameEquals('Success');
        $this->assertContainerAttributeExists('items');

        $itemCount = count($this->container->getAttribute('items'));
        $expectedItems = 10;
        $this->assertEquals($expectedItems, $itemCount);
    }

    public function testTermSearchDefaultList()
    {
        $this->runActionWithParameters('read', array('search_phrase' => 'zeit'));
        $this->assertViewNameEquals('Success');
        $this->assertContainerAttributeExists('items');

        $itemCount = count($this->container->getAttribute('items'));
        $expectedItems = 1;
        $this->assertEquals($expectedItems, $itemCount);
    }

    public function testWildcardSearchDefaultList()
    {
        $this->runActionWithParameters('read', array('search_phrase' => 'tage*'));
        $this->assertViewNameEquals('Success');
        $this->assertContainerAttributeExists('items');

        $itemCount = count($this->container->getAttribute('items'));
        $expectedItems = 3;
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
