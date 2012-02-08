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
        $stats = $provider->fetchDistrictStatistics($daysBack);
        ksort($stats);

        $this->setAttribute('statistics', $stats);
        $this->setAttribute('days_back', $daysBack);

        return 'Success';
    }
}

?>