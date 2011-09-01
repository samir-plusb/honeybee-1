<?php

class ImperiaDataImportConfigTest extends ImportConfigBaseTestCase
{
    const CFG_FIXTURE = 'data/polizeimeldungen.config.import.php';

    protected function getConfigImplementor()
    {
        return 'ImperiaDataImportConfig';
    }

    protected function getConfigFixturePath()
    {
        $baseDir = AgaviConfig::get('core.testing_dir') . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;

        return $baseDir . self::CFG_FIXTURE;
    }
}

?>
