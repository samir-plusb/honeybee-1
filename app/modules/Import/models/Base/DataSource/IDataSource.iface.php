<?php

interface IDataSource
{
    /**
     * @param IImportConfig $config
     */
    public function __construct(IImportConfig $config);

    /**
     * @return IDataRecord
     */
    public function nextRecord();
}

?>
