<?php

class ImperiaImportConfigTest extends ImportConfigBaseTestCase
{
    const CFG_FIXTURE = 'data/import/imperia/polizeimeldungen.config.import.php';

    protected function getConfigImplementor()
    {
        return 'CouchDbDataImportConfig';
    }

    protected function getConfigFixturePath()
    {
        $baseDir = AgaviConfig::get('core.testing_dir') . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;

        return $baseDir . self::CFG_FIXTURE;
    }
}

?>