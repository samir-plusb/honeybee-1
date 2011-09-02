<?php

class ImperiaDataImport extends BaseCouchDbImport
{
    protected function processRecord(IDataRecord $record)
    {
        return $record->toArray();
    }
}

?>
