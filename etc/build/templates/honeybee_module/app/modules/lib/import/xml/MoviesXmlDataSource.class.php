<?php

class MoviesXmlDataSource extends ArrayDataSource
{
    protected function initData()
    {
        $xmlParser = new MoviesXmlParser(
            realpath($this->config->getSetting(MoviesXmlDataSourceConfig::CFG_SCREENINGS_FILE_PATH))
        );
        $filePath = realpath($this->config->getSetting(MoviesXmlDataSourceConfig::CFG_FILE_PATH));
        if (!$filePath)
        {
            throw new DataSourceException("File: " . $filePath . " can not be resolved.");
        }
        return $xmlParser->parseXml($filePath);
    }
}
