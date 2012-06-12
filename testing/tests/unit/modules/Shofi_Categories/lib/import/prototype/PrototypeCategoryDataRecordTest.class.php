<?php

class PrototypeCategoryDataRecordTest extends AgaviPhpUnitTestCase
{
    const CFG_JSON_FIXTURE = 'Shofi_Categories/import/prototype/fixture.category.json';

    const CFG_DATA_FIXTURE = 'Shofi_Categories/import/prototype/fixture.category.php';

    protected $record;

    public function setUp()
    {
        parent::setUp();

        $this->record = new PrototypeCategoryDataRecord(
            $this->loadFixture(),
            new DataRecordConfig(
                array(
                    DataRecordConfig::CFG_ORIGIN => 'protoype.category',
                    DataRecordConfig::CFG_SOURCE => 'protoype.category'
                )
            )
        );
    }


    protected function loadFixture()
    {
        $path = realpath(AgaviConfig::get('core.fixtures_dir') . self::CFG_JSON_FIXTURE);
        $data = file_get_contents($path);
        return json_decode($data, TRUE);
    }

    public function testToArray()
    {
        $data = $this->record->toArray();
        $path = realpath(AgaviConfig::get('core.fixtures_dir') . self::CFG_DATA_FIXTURE);
        $expected = include $path;
        foreach ($expected as $name => $curExpect)
        {
            $this->assertEquals($curExpect, $data[$name], "Value for '" . $name . "' does not match expected: " . print_r($curExpect, TRUE));
        }
    }

    // @codeCoverageIgnoreEnd
}

?>