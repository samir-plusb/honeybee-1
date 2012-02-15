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
class DpaNitfDataRecordTest extends DataRecordBaseTestCase
{
    const CFG_XML_FIXTURE = 'import/newswire/dpa-ServiceLine/iptc-zin-20110527-18-dpa_30598702.xml';

    const CFG_DATA_FIXTURE = 'import/newswire/iptc-zin-20110527-18-dpa_30598702.php';

    public function testToArray()
    {
        $values = $this->dataRecord->toArray();
    }

    protected function getDataRecordClass()
    {
        return 'DpaNitfNewswireDataRecord';
    }

    protected function getRecordXmlFixturePath()
    {
        return self::CFG_XML_FIXTURE;
    }

    protected function getRecordResultFixturePath()
    {
        return self::CFG_DATA_FIXTURE;
    }

    protected function getDataRecordSource()
    {
        return 'dpa - Deutsche Presse-Agentur GmbH';
    }

    protected function getDataRecordOrigin()
    {
        return AgaviConfig::get('core.fixtures_dir') . $this->getRecordXmlFixturePath();
    }
}

?>