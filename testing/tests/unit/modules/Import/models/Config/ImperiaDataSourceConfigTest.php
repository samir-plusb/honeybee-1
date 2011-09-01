<?php

class ImperiaDataSourceConfigTest extends ImportConfigBaseTestCase
{
    const CFG_FIXTURE = 'data/polizeimeldungen.config.datasource.php';

    protected function getConfigImplementor()
    {
        return 'ImperiaDataSourceConfig';
    }

    protected function getConfigFixturePath()
    {
        $baseDir = AgaviConfig::get('core.testing_dir') . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;

        return $baseDir . self::CFG_FIXTURE;
    }
}

?>
