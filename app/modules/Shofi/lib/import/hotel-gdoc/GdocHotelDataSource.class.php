<?php

/**
 * The GdocHotelDataSource class is a concrete implementation of the BaseDataSource base class.
 * It provides fetching hotels from a google docs spreadsheet.
 *
 * @version         $Id: GdocHotelDataSource.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         Shofi
 * @subpackage      Import/Hotel-Gdoc
 */
class GdocHotelDataSource extends ArrayDataSource
{
    /**
     * Setup the datasource.
     */
    protected function init()
    {
        $email = $this->config->getSetting(GdocHotelDataSourceConfig::CFG_EMAIL);
        $password = $this->config->getSetting(GdocHotelDataSourceConfig::CFG_PASSWORD);
        $this->service = new GoogleDocsService($email, $password);
        
        parent::init();
    }

    /**
     * Setup the datasource's data (called from our parent's init).
     */
    protected function initData()
    {
        return $this->service->getDocumentData(
            $this->config->getSetting(GdocHotelDataSourceConfig::CFG_DOC_ID)
        );
    }
}

?>
