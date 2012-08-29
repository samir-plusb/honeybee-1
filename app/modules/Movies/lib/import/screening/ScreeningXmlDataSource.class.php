<?php

class ScreeningXmlDataSource extends ArrayDataSource
{
    protected function initData()
    {
        $xmlParser = new ScreeningXmlParser();
        $filePath = realpath($this->config->getSetting(ScreeningXmlDataSourceConfig::CFG_FILE_PATH));
        if (! $filePath)
        {
            throw new DataSourceException("File: " . $filePath . " can not be resolved.");
        }
        return $xmlParser->parseXml($filePath);
    }

    protected function getCurrentOrigin()
    {
        return basename(realpath($this->config->getSetting(ScreeningXmlDataSourceConfig::CFG_FILE_PATH)));
    }
}
