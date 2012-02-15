<?php

class PoliceReportDataRecordTest extends DataRecordBaseTestCase
{
    const CFG_XML_FIXTURE = 'import/imperia/polizeimeldung.article2.xml';

    const CFG_DATA_FIXTURE = 'import/imperia/polizeimeldung.article2.php';

    const FIXTURE_SERVER_SETTING = 'core.fixture_server_url';

    private static $originalImperiaUrl;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$originalImperiaUrl = AgaviConfig::get(ImperiaDataRecord::LINK_BASE_URL_SETTING);
        AgaviConfig::set(
            ImperiaDataRecord::LINK_BASE_URL_SETTING,
            AgaviConfig::get(self::FIXTURE_SERVER_SETTING)
        );
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        AgaviConfig::set(
            ImperiaDataRecord::LINK_BASE_URL_SETTING,
            self::$originalImperiaUrl
        );
    }

    protected function getRecordXmlFixturePath()
    {
        return self::CFG_XML_FIXTURE;
    }

    protected function getRecordResultFixturePath()
    {
        return self::CFG_DATA_FIXTURE;
    }

    protected function getDataRecordClass()
    {
        return 'PoliceReportDataRecord';
    }

    protected function getDataRecordSource()
    {
        return 'polizeimeldungen';
    }

    protected function getDataRecordOrigin()
    {
        return 'polizeimeldungen/test';
    }
}

?>