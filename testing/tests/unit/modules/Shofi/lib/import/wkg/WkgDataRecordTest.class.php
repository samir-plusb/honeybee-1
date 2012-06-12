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
class WkgDataRecordTest extends DataRecordBaseTestCase
{
    const CFG_XML_FIXTURE = 'Shofi/import/wkg/listing.fixture.xml';

    const CFG_DATA_FIXTURE = 'Shofi/import/wkg/listing.fixture.php';

    protected function getDataRecordClass()
    {
        return 'WkgDataRecord';
    }

    protected function getRecordXmlFixturePath()
    {
        return self::CFG_XML_FIXTURE;
    }

    protected function getDataRecordSource()
    {
        return 'wgk';
    }

    protected function getDataRecordOrigin()
    {
        return AgaviConfig::get('core.fixtures_dir') . $this->getRecordXmlFixturePath();
    }

     // As these are run outside of the code coverage's scope, they allways will be marked as non-executed.
    // @codeCoverageIgnoreStart

    protected function getRecordResultFixturePath()
    {
        return self::CFG_DATA_FIXTURE;
    }

    /**
     * This method serves as the data provider for the testGetValue test.
     * It returns an array of expected setting name/value pairs.
     *
     * @return      array
     */
    public function provideExpectedGetterParams()
    {
        $ret = array();

        foreach ($this->loadDataRecordResultFixture() as $propName => $value)
        {
            $getterMethod = 'get' . ucfirst($propName);
            if ('location' === $propName)
            {
                $ret[] = array('expected' => ItemLocation::fromArray($value), 'getterName' => $getterMethod);
            }
            else
            {
                $ret[] = array('expected' => $value, 'getterName' => $getterMethod);
            }
        }

        return $ret;
    }

    // @codeCoverageIgnoreEnd
}

?>