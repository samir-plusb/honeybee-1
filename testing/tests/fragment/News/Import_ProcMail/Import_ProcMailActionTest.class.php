<?php

/**
 * @agaviIsolationDefaultContext web
 */
class Import_ProcMailActionTest extends AgaviActionTestCase
{
    const MAIL_FIXTURE_PATH = 'News/import/mail/testmail';

    // As these are run outside of the code coverage's scope, they allways will be marked as non-executed.
    // @codeCoverageIgnoreStart

    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->contextName = 'console';
        $this->moduleName = 'News';
        $this->actionName = 'Import_ProcMail';
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
            'testmail' => array('foobar' => 'moo')
         ));

        $this->assertViewNameEquals('Error');
    }

    public function testRunImportSingleSuccess()
    {
        $this->runActionWithParameters('write', array(
            'testmail' => $this->getFixtureData())
        );
        $this->assertValidatedArgument(ImportMailValidator::DEFAULT_PARAM_EXPORT);
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
                    AgaviConsoleRequestDataHolder::SOURCE_FILES => $arguments
                )
            )
        );

        $this->runAction();
    }

    public function getFixtureData()
    {
        $path = AgaviConfig::get('core.fixtures_dir') . self::MAIL_FIXTURE_PATH;
        $size = filesize($path);

        return array(
            'name' => 'testmail',
            'type' => 'application/octet-stream',
            'size' => $size,
            'contents' => file_get_contents($path),
            'error' => UPLOAD_ERR_OK,
            'is_uploaded_file' => false,
        );
    }
}

?>