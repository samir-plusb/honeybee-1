<?php

class ImperiaImportConfigTest extends ImportConfigBaseTestCase
{
    const CFG_FIXTURE = 'data/import/imperia/config.import.php';

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