<?php

/**
 * The Items_StatsAction is repsonseable for loading the news statistics.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Items
 * @subpackage      Mvc
 */
class Items_StatsAction extends ItemsBaseAction
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
        $provider = new NewsStatisticProvider();
        $daysBack = $parameters->getParameter('days_back', 5);
        $district = $parameters->getParameter('district', NewsStatisticProvider::DISTRICT_ALL);
        $stats = $provider->fetchDistrictStatistics($daysBack, $district);

        $this->setAttribute('statistics', $stats);
        $this->setAttribute('days_back', $daysBack);

        return 'Success';
    }

    public function handleError(AgaviRequestDataHolder $parameters)
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