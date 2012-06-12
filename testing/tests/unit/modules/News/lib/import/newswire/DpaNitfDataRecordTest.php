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
    const CFG_XML_FIXTURE = 'News/import/newswire/dpa-ServiceLine/iptc-zin-20110527-18-dpa_30598702.xml';

    const CFG_DATA_FIXTURE = 'News/import/newswire/iptc-zin-20110527-18-dpa_30598702.php';

    protected function getDataRecordClass()
    {
        return 'DpaNitfNewswireDataRecord';
    }

    protected function getRecordXmlFixturePath()
    {
        return self::CFG_XML_FIXTURE;
    }

    protected function getDataRecordSource()
    {
        return 'dpa - Deutsche Presse-Agentur GmbH';
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

    // @codeCoverageIgnoreEnd
}

?>