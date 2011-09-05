<?php

class ImperiaDataImportMockUp extends ImperiaDataImport
{
    const SETTING_OUTPUT_FILE = 'output_file';

    protected function init(IDataSource $dataSource)
    {
        parent::init($dataSource);
        
        $importTargetFile = $this->config->getSetting(self::SETTING_OUTPUT_FILE);
        file_put_contents($importTargetFile, '');
    }

    protected function importData(array $data)
    {
        parent::importData($data);
        
        $importTargetFile = $this->config->getSetting(self::SETTING_OUTPUT_FILE);

        if (empty($importTargetFile) || !is_string($importTargetFile))
        {
            throw new DataImportException("Missing or invalid output_file setting encountered for mockimport class. Path: " . $importTargetFile);
        }

        file_put_contents($importTargetFile, var_export($data, true), FILE_APPEND);
    }
}

?>