<?php

class Import_TriggerMailAction extends ImportBaseAction
{
    public function executeWrite(AgaviRequestDataHolder $rd)
    {
        $rawMail = $rd->getParameter(
            ImportMailValidator::DEFAULT_PARAM_EXPORT
        );

        $importFactory = $this->createImportFactory();
        $dataImport = $importFactory->createDataImport(self::DATAIMPORT_COUCHDB);
        $dataSources = array(
            $importFactory->createDataSource(
                self::DATASOURCE_IMAP,
                array(
                    ImapDataSourceConfig::PARAM_MAILITEM => $rawMail
                )
            )
        );

        return $this->runImports($dataImport, $dataSources);
    }
}

?>