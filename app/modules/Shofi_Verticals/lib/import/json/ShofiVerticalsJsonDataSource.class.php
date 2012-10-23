<?php

class ShofiVerticalsJsonDataSource extends ArrayDataSource
{
    protected function initData()
    {
        $filePath = realpath($this->config->getSetting(
            ShofiVerticalsJsonDataSourceConfig::CFG_FILE_PATH
        ));
        return json_decode(file_get_contents($filePath), TRUE);
    }
}

?>
