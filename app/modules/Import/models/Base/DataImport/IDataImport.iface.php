<?php

/**
 * IDataImport implementations are responseable for importing IDataRecords 
 * to any required location.
 * They shall receive latter IDataRecords from a given IDataSource implementation.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Base/Config
 */
interface IDataImport
{
    /**
     * Create a new IDataImport instance.
     * 
     * @param       IImportConfig $config
     */
    public function __construct(IImportConfig $config);

    /**
     * Imort all IDataRecords provided by the given IDataSource.
     * 
     * @param       IDataSource $dataSource
     */
    public function run(IDataSource $dataSource);
}

?>