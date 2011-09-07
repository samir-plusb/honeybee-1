<?php

/**
 *
 * @copyright BerlinOnline
 * @version $Id$
 * @package Import
 */
class Import_ImperiaAction extends ImportBaseAction
{
    const DEFAULT_CONFIG_FILE = 'polizeimeldungen.xml';

    const PARAM_CONFIG_NAME = 'config';

    public function executeWrite(AgaviRequestDataHolder $parameters)
    {
        $importFactory = new DataImportFactory(
            $parameters->getParameter(self::PARAM_CONFIG_NAME, $this->getImportConfigDirectory())
        );

        $import = $importFactory->createDataImport('ImperiaDataImportConfig');
        $dataSource = $importFactory->createDataSource('ImperiaDataSourceConfig');

        if (!$import->run($dataSource))
        {
            return 'Error';
        }

        return 'Success';
    }

    private function getImportConfigDirectory()
    {
        return AgaviConfig::get('core.app_dir') . DIRECTORY_SEPARATOR .
            'modules' . DIRECTORY_SEPARATOR .
            'Import' . DIRECTORY_SEPARATOR .
            'config' . DIRECTORY_SEPARATOR .
            'imports' . DIRECTORY_SEPARATOR .
            self::DEFAULT_CONFIG_FILE;
    }

}

?>