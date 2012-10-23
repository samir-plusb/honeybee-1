<?php

class CouchDbDataImportConfigTest extends ImportConfigBaseTestCase
{
    const CFG_FIXTURE = 'Import/config/config.import.php';

    protected function getConfigImplementor()
    {
        return 'CouchDbDataImportConfig';
    }

    protected function getConfigFixturePath()
    {
        return AgaviConfig::get('core.fixtures_dir') . self::CFG_FIXTURE;
    }
}

?>