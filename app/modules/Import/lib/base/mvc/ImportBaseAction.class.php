<?php

/**
 * The base action from which all Import module actions inherit.
 *
 * @version         $Id: $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Mvc
 */
class ImportBaseAction extends ProjectBaseAction
{
    /**
     * Name of our couchdb data import definition.
     */
    const DATAIMPORT_COUCHDB = 'couchdb';

    /**
     * Name of our workflow import definition.
     */
    const DATAIMPORT_WORKFLOW = 'workflow';

    /**
     * Name of our imperia data source definition.
     */
    const DATASOURCE_IMPERIA = 'imperia';

    /**
     * Name of our procmail data source definition.
     */
    const DATASOURCE_PROCMAIL = 'procmail';

    /**
     * Handle our validation(write) errors.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @return      string Name of the error view to use.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function handleWriteError(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        return 'Error';
    }

    /**
     * Creates and returns a new ImportFactory instance.
     *
     * @return      ImportFactory
     */
    protected function createImportFactory()
    {
        return new ImportFactory(
            new ImportFactoryConfig(
                AgaviConfig::get('import.config_dir')
            )
        );
    }

    /**
     *
     * @param       IDataImport $dataImport
     * @param       array $dataSources
     *
     * @return      string
     */
    protected function runImports(IDataImport $dataImport, array $dataSources)
    {
        $view = 'Success';

        foreach ($dataSources as $dataSource)
        {
            try
            {
                $dataImport->run($dataSource);
            }
            catch(AgaviAutoloadException $e)
            {
                /* @todo better exception handling */
                $this->setAttribute('errors', array($e->getMessage()));
                $this->logError("An unexpected error occured during import: " . $e->getMessage());
                $view = 'Error';
            }
        }

        return $view;
    }

    public function isSecure()
    {
        return FALSE;
    }
}

?>