<?php

interface IDataImport
{
    /**
     * @param IImportConfig $config
     */
    public function __construct(IImportConfig $config);

    /**
     * @param IDataSource $dataSource
     */
    public function run(IDataSource $dataSource);
}

?>