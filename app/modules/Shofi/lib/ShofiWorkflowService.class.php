<?php

/**
 *
 * @version $Id: ShofiWorkflowService.class.php -1   $
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Shofi
 */
class ShofiWorkflowService extends BaseWorkflowService
{
    const ITEM_IMPLEMENTOR = 'ShofiWorkflowItem';

    private static $instance;

    public static function getInstance()
    {
        if (NULL === self::$instance)
        {
            self::$instance = new ShofiWorkflowService('shofi');
        }
        return self::$instance;
    }

    public function findItemByImportIdentifier($importIdentifier)
    {
        $listConfig = ListConfig::fromArray(AgaviConfig::get('shofi.list_config'));
        $shofiFinder = ShofiFinder::create($listConfig);
        
        return $shofiFinder->findItemByImportIdentifier($importIdentifier);
    }

    public function findAllImportIdentifiers(IDataSource $dataSource)
    {
        $listConfig = ListConfig::fromArray(AgaviConfig::get('shofi.list_config'));
        $shofiFinder = ShofiFinder::create($listConfig);
        
        return $shofiFinder->findAllImportIdentifiers($dataSource);
    }

    public function localizeItem(ShofiWorkflowItem $shofiItem, $force = FALSE)
    {
        $wasLocalized = FALSE;
        $location = $shofiItem->getCoreItem()->getLocation();
        if (FALSE === $force && NULL !== $location->asGeoPoint())
        {
            $this->logInfo(
                sprintf("No need for localization as item: '%s' allready has coords", $shofiItem->getIdentifier())
            );
        }
        else
        {
            $url = sprintf('%s?string=', AgaviConfig::get('shofi.localize_api_url'));
            // create 'geoText' that we will query the localize api for.
            $city = $location->getCity();
            $queryValues = array(
                $location->getStreet(),
                $location->getHousenumber(),
                $city ? $city : 'Berlin',
                $location->getPostalCode()
            );
            $result = $this->fetchGeoCoordinates($queryValues);
            // if everyting worked hydrate the location data et voila
            if (0 < $result['items_count'])
            {
                $locationData = $result[0];
                // @todo: set street and housenumber if housenumber was inside the street field ...
                $location->applyValues(array(
                    'neighborhood' => isset($locationData['neighborhood']) ? $locationData['neighborhood'] : '',
                    'district' => isset($locationData['district']) ? $locationData['district'] : NULL,
                    'administrativeDistrict' => isset($locationData['administrative district']) ? 
                        $locationData['administrative district'] : NULL,
                    'coordinates' => array(
                        'lon' => $locationData['longitude'],
                        'lat' => $locationData['latitude']
                    )
                ));
                $wasLocalized = TRUE;
            }
        }
        return $wasLocalized;
    }

    public function fetchGeoCoordinates(array $queryValues)
    {
        $url = sprintf('%s?string=', AgaviConfig::get('shofi.geo_service_url'));
        foreach ($queryValues as $queryValue)
        {
            $queryValue = trim($queryValue);
            if ($queryValue)
            {
                $url .= '+'.urlencode($queryValue);
            }
        }
        // init our curl handle
        $curl = ProjectCurl::create();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'X-Requested-With: XMLHttpRequest',
            'Content-Type:application/json',
            'Accept:application/json; charset=utf-8'
        ));
        // fire the request
        $apiResponse = curl_exec($curl);
        $this->logInfo(
            "Received following response for localization of '" . $url . "': " . PHP_EOL . $apiResponse
        );
        // and handle the response
        if (curl_error($curl))
        {
            error_log(
                __METHOD__ . " - Failed localizing item: " . print_r($queryValues, TRUE) .
                 PHP_EOL . "Error: " . curl_error($curl)
            );
            return FALSE;
        }
        else
        {
            return json_decode($apiResponse, TRUE);
        }
    }

    protected function getWorkflowItemImplementor()
    {
        return self::ITEM_IMPLEMENTOR;
    }
}
