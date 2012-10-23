<?php
/**
 * Test DPA NITF news wire messages
 *
 * @package Testing
 * @subpackage Import
 * @version $Id:$
 * @author tay
 *
 */
class PrototypeDataRecordTest extends AgaviPhpUnitTestCase
{
    const CFG_JSON_FIXTURE = 'Shofi/import/prototype/fixture.json';

    const CFG_DATA_FIXTURE = 'Shofi/import/prototype/fixture.php';

    protected $record;

    public function setUp()
    {
        parent::setUp();

        $this->record = new PrototypeDataRecord(
            $this->loadFixture(),
            new DataRecordConfig(
                array(
                    DataRecordConfig::CFG_ORIGIN => 'protoype',
                    DataRecordConfig::CFG_SOURCE => 'protoype'
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
            $this->assertEquals($curExpect, $data[$name], "Value " . $data[$name] . " for property/key " . $name . " does not match expected " . $curExpect);
        }
    }

    protected function areAttachmentEquals(array $leftAttach, array $rightAttach)
    {
        foreach ($leftAttach as $prop => $val)
        {
            if (! isset($rightAttach[$prop]) || $rightAttach[$prop] !== $val)
            {
                return FALSE;
            }
        }
        return TRUE;
    }

    // @codeCoverageIgnoreEnd
}

?>