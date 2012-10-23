<?php

class ExtractDateActionTest extends AgaviActionTestCase
{
    // As these are run outside of the code coverage's scope, they allways will be marked as non-executed.
    // @codeCoverageIgnoreStart

    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->contextName = 'web';
        $this->moduleName = 'News';
        $this->actionName = 'Api_ExtractDate';
    }

    public function provideTestRunImportArgs()
    {
        $data = array(
            array(
                'expected' => '01.01.2012',
                'dateText' => '1 Jan'
            ),
            array(
                'expected' => '04.12.2010',
                'dateText' => '04.12.2010'
            )
        );
        return $data;
    }

    // @codeCoverageIgnoreEnd

    /**
     *
     * @param       string $importName
     * @param       string $datasourceNames
     *
     * @dataProvider provideTestRunImportArgs
     */
    public function testExtractDate($expected, $dateText)
    {
        $this->runActionWithParameters('read', array('date_text' => $dateText));
        $this->assertViewNameEquals('Success');
        $this->assertContainerAttributeExists('date');
        $this->assertContainerAttributeEquals($expected, 'date');
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
