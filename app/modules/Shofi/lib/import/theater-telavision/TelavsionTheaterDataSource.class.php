<?php

/**
 * The TheaterDataSource class is a concrete implementation of the BaseDataSource base class.
 * It provides fetching xml based theater data.
 *
 * @version         $Id: WkgDataSource.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         Shofi
 * @subpackage      Import/Theater
 */
class TelavsionTheaterDataSource extends ArrayDataSource
{
    /**
     * Setup the datasource.
     */
    protected function initData()
    {
        $xmlParser = new TelavsionTheaterXmlParser();
        $filePath = realpath($this->config->getSetting(
            TelavsionTheaterDataSourceConfig::CFG_FILE_PATH)
        );
        if (!$filePath)
        {
            throw new DataSourceException("File: " . $filePath . " can not be resolved.");
        }
        return $xmlParser->parseXml($filePath);
    }
}

?>
