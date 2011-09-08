<?php

/**
 * IDataSource implementations are responseable for wrapping data access to any desired data source.
 * IDataSource instances provide the reflected data in form of IDataRecord implementations
 * and expose these iteratively in order to leave memory control the concrete implementors.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Base
 */
interface IDataSource
{
    /**
     * Create a new IDataSource instance.
     * 
     * @param       IImportConfig $config
     */
    public function __construct(IImportConfig $config);

    /**
     * Pulls the next set of data from our source
     * and returns it in form of a corresponding IDataRecord implementation.
     * The concrete IDataRecord implementation that an IDataSource shall use
     * should be provided by the IDataRecord's config object.
     * 
     * @return      IDataRecord
     */
    public function nextRecord();
}

?>