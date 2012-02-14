<?php

/**
 * The News_StatsAction is repsonseable for loading the news statistics.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         News
 * @subpackage      Mvc
 */
class News_StatsAction extends NewsBaseAction
{
    /**
     * Execute the read logic for this action.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @return      string The name of the view to execute.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeRead(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $provider = new NewsStatisticProvider(
            new Elastica_Client(
                array(
                    'host'      => AgaviConfig::get('elasticsearch.host', 'localhost'),
                    'port'      => AgaviConfig::get('elasticsearch.port', 9200),
                    'transport' => AgaviConfig::get('elasticsearch.transport', 'Http')
                )
            ),
            $this->getContext()->getDatabaseConnection('CouchWorkflow')
        );

        $daysBack = $parameters->getParameter('days_back', 5);
        $district = $parameters->getParameter('district', NewsStatisticProvider::DISTRICT_ALL);
        $stats = $provider->fetchDistrictStatistics($daysBack, $district);

        $this->setAttribute('statistics', $stats);
        $this->setAttribute('days_back', $daysBack);

        return 'Success';
    }

    /**
     * Handle read errors, hence failed validation on the incoming parameters.
     *
     * @param AgaviRequestDataHolder $parameters
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function handleReadError(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $errors = array();
        $validation_manager = $this->getContainer()->getValidationManager();
        foreach ($validation_manager->getErrorMessages() as $error)
        {
            $errors[] = $error['message'];
        }
        $this->setAttribute('errors', $errors);
        return 'Error';
    }
}

?>