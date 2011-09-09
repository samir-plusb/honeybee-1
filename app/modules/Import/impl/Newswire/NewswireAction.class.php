<?php
/**
 *
 * @copyright BerlinOnline
 * @version $Id$
 * @package Import
 * @subpackage Newswire
 */
class Import_NewswireAction extends ImportBaseAction
{
    const PARAM_CONFIG_NAME = 'config';

    /**
     *
     * @param AgaviRequestDataHolder $parameters
     */
    public function executeWrite(AgaviRequestDataHolder $parameters)
    {
        $importFactory = new DataImportFactory(
            $this->getImportConfigFile($parameters->getParameter(self::PARAM_CONFIG_NAME, 'newswire-dpa.xml'))
        );

        /* @todo Remove debug code NewswireAction.class.php from 08.09.2011 */
        error_log(__CLASS__.":".__FUNCTION__.":".__LINE__,3,'/tmp/log');
        error_log($this->getImportConfigFile($parameters->getParameter(self::PARAM_CONFIG_NAME, 'newswire-dpa.xml')),3,'/tmp/log');

        $import = $importFactory->createDataImport();
        $dataSource = $importFactory->createDataSource();

        if (!$import->run($dataSource))
        {
            return 'Error';
        }

        return 'Input';
    }

    /**
     * get data import config file path
     *
     * @param string $sourceName
     * @return string path to config file
     */
    private function getImportConfigFile($sourceName)
    {
        if ($sourceName[0] == '/')
        {
            return $sourceName;
        }
        else
        {
            return AgaviConfig::get('core.app_dir') . DIRECTORY_SEPARATOR .
                'modules' . DIRECTORY_SEPARATOR .
                'Import' . DIRECTORY_SEPARATOR .
                'config' . DIRECTORY_SEPARATOR .
                'imports' . DIRECTORY_SEPARATOR .
                $sourceName;
        }
    }

}

?>