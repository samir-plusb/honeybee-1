<?php

/**
 * IDataImport implementations are responseable for importing IDataRecords
 * to any required location.
 * They shall receive latter IDataRecords from a given IDataSource implementation.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage DataImport
 */
interface IDataImport
{
    /**
     * Create a new IDataImport instance.
     *
     * @param       IConfig $config
     */
    public function __construct(IConfig $config);

    /**
     * Imort all IDataRecords provided by the given IDataSource.
     *
     * @param       IDataSource $dataSource
     */
    public function run(IDataSource $dataSource);
}

?>