<?php

/**
 * The Shofi_Api_ExtractLocationAction is repsonseable handling location extraction api requests.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Shofi
 * @subpackage      Mvc
 */
class Shofi_Api_LocalizeAction extends ShofiBaseAction
{

    /**
     * Execute the read logic for this action, hence extract the data.
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
        $queryValues = array(
            $parameters->getParameter('street', FALSE),
            $parameters->getParameter('housenumber', FALSE),
            $parameters->getParameter('city', 'Berlin'),
            $parameters->getParameter('postal_code', FALSE)
        );
        $service = ShofiWorkflowService::getInstance();
        $coords = $service->fetchGeoCoordinates($queryValues);
        $this->setAttribute('location', $coords);
        return 'Success';
    }
}