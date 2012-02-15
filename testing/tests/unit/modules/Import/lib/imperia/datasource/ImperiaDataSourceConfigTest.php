<?php

class ImperiaDataSourceConfigTest extends ImportConfigBaseTestCase
{
    const CFG_FIXTURE = 'import/config/config.datasource.php';

    protected function getConfigImplementor()
    {
        return 'ImperiaDataSourceConfig';
    }

    protected function getConfigFixturePath()
    {
        return AgaviConfig::get('core.fixtures_dir') . self::CFG_FIXTURE;
    }
}

?>