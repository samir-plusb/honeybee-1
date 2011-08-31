<?php

interface IDataSource
{
    /**
     * @param IGenericConfig $config
     */
    public function __construct(IGenericConfig $config);

    /**
     * @return IDataRecord
     */
    public function nextRecord();
}

?>
