<?php

interface IDataImportFactory
{
    public function createDataImport($configClass, array $parameters = array());

    public function createDataSource($configClass, array $parameters = array());
}

?>
