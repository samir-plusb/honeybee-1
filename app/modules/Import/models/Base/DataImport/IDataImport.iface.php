<?php

interface IDataImport
{
    /**
     * @param IGenericConfig $config
     */
    public function __construct(IImportConfig $config);

    /**
     * @param IDataSource $dataSource
     */
    public function run(IDataSource $dataSource);
}

?>
