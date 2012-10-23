<?php

class ExtractLocationActionTest extends AgaviActionTestCase
{
    const JSON_FIXTURE = 'News/location.api.fixture.json';

    // As these are run outside of the code coverage's scope, they allways will be marked as non-executed.
    // @codeCoverageIgnoreStart

    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->contextName = 'web';
        $this->moduleName = 'News';
        $this->actionName = 'Api_ExtractLocation';
    }

    // @codeCoverageIgnoreEnd

    public function testDefaultRead()
    {
        $this->runActionWithParameters('read', array('geo_text' => 'Schlossstrasse 2+Berlin'));
        $this->assertViewNameEquals('Success');
        $this->assertContainerAttributeExists('location');
        $fixturePath = AgaviConfig::get('core.fixtures_dir') . DIRECTORY_SEPARATOR . self::JSON_FIXTURE;
        $expectedLocation = json_decode(file_get_contents($fixturePath), TRUE);
        $this->assertContainerAttributeEquals($expectedLocation, 'location');
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
