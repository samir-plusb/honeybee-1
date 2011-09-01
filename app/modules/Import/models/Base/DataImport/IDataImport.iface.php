<?php

interface IDataImport
{
    /**
     * @param IGenericConfig $config
     */
    public function __construct(IConfig $config);

    /**
     * @param IDataSource $dataSource
     */
    public function run(IDataSource $dataSource);
}

?>
