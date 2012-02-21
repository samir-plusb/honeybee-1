<?php

class ExtractLocationActionTest extends AgaviActionTestCase
{
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
        $this->runActionWithParameters('read', array('geo_text' => 'Schlossstrasse 2, Berlin'));
        $this->assertViewNameEquals('Success');
        $this->assertContainerAttributeExists('location');
        $expectedLocation = array (
            array (
                'street' => 'Schloßplatz 2',
                'uzip' => '10178',
                'neighborhood' => 'Mitte',
                'district' => 'Mitte',
                'administrative district' => 'Mitte',
                'longitude' => 13.400815,
                'latitude' => 52.5158399,
            ),
            array (
                'street' => 'Schloßstraße 2',
                'uzip' => '12163',
                'neighborhood' => 'Steglitz',
                'district' => 'Steglitz',
                'administrative district' => 'Steglitz-Zehlendorf',
                'longitude' => 13.32725,
                'latitude' => 52.46445,
            ),
            array (
                'street' => 'Schloßstraße 2',
                'uzip' => '14059',
                'neighborhood' => 'Charlottenburg',
                'district' => 'Charlottenburg',
                'administrative district' => 'Charlottenburg-Wilmersdorf',
                'longitude' => 13.29492,
                'latitude' => 52.51802,
            ),
            array (
                'street' => 'Schloßstraße 2',
                'uzip' => '13467',
                'neighborhood' => 'Hermsdorf',
                'district' => 'Reinickendorf',
                'administrative district' => 'Reinickendorf',
                'longitude' => 13.3136246,
                'latitude' => 52.616895,
            ),
            array (
                'street' => 'Schloßstraße 2',
                'uzip' => '13507',
                'neighborhood' => 'Tegel',
                'district' => 'Reinickendorf',
                'administrative district' => 'Reinickendorf',
                'longitude' => 13.2832885,
                'latitude' => 52.590951,
            ),
            array (
                'street' => 'Schloßallee 2',
                'uzip' => '13156',
                'neighborhood' => 'Niederschönhausen',
                'district' => 'Pankow',
                'administrative district' => 'Pankow',
                'longitude' => 13.4202616,
                'latitude' => 52.5813313,
            ),
            'items_count' => 6,
        );
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
