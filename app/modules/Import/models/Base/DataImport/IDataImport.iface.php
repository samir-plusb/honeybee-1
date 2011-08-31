<?php

interface IDataImport
{
    /**
     * @param IGenericConfig $config
     */
    public function __construct(IGenericConfig $config);

    /**
     * 
     */
    public function run();
}

?>
