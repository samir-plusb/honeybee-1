<?php

class PoliceReportDataRecordTest extends DataRecordBaseTestCase
{
    const CFG_XML_FIXTURE = 'data/import/imperia/polizeimeldung.article2.xml';

    const CFG_DATA_FIXTURE = 'data/import/imperia/polizeimeldung.article2.php';
    
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
}

?>