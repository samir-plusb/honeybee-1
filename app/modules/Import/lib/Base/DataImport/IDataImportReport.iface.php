<?php

interface IDataImportReport
{
    public function addRecordSuccess(IDataRecord $record, $msg = '');
    
    public function addRecordError(IDataRecord $record, $msg = '');
    
    public function getSuccessCount();
    
    public function getErrors();
}

?>