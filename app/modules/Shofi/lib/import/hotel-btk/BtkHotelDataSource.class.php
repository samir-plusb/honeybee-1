<?php

/**
 * The HotelDataSource class is a concrete implementation of the BaseDataSource base class.
 * It provides fetching xml based theater data.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         Shofi
 * @subpackage      Import/Hotel
 */
class BtkHotelDataSource extends ArrayDataSource
{
    /**
     * Setup the datasource.
     */
    protected function initData()
    {
        $xmlParser = new BtkHotelXmlParser();
        $filePath = realpath($this->config->getSetting(BtkHotelDataSourceConfig::CFG_FILE_PATH));
        if (!$filePath)
        {
            throw new DataSourceException("File: " . $filePath . " can not be resolved.");
        }
        return $xmlParser->parseXml($filePath);
    }
}

?>
