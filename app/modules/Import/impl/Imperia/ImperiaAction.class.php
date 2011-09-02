<?php

/**
 *
 * @copyright BerlinOnline
 * @version $Id$
 * @package Import
 */
class Import_ImperiaAction extends ImportBaseAction
{
    const PARAM_CONFIG_FILE = 'import_config';

	public function executeWrite(AgaviRequestDataHolder $rd)
	{
        $configFile = $rd->getParameter(self::PARAM_CONFIG_FILE);
        $config = new ImperiaImportFactoryConfig($configFile);

        $importFactory = new DataImportFactory($config);

        $import = $importFactory->createImport('ImperiaDataImportConfig');
        $dataSource = $importFactory->createDataSource('ImperiaDataSourceConfig');

        if (!$import->run($dataSource))
        {
            
        }

		return 'Success';
	}

    private function getImportConfigDirectory()
    {
        $baseDir = AgaviConfig::get('core.config_dir') . DIRECTORY_SEPARATOR . 'imports' . DIRECTORY_SEPARATOR;

        return $baseDir . self::PARAM_CONFIG_FILE;

    }
}

?>
