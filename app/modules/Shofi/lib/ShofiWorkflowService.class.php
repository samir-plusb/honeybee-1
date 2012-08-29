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

    public function localizeItem(ShofiWorkflowItem $shofiItem, $force = FALSE)
    {
        $location = $shofiItem->getCoreItem()->getLocation();
        $coords = $location->getCoordinates();
        if (FALSE === $force && ($coords && ! empty($coords['lon']) && ! empty($coords['lat'])))
        {
            $this->logInfo(
                sprintf("No need for localization as item: '%s' allready has coords", $shofiItem->getIdentifier())
            );
            return FALSE;
        }
        $url = sprintf(
            '%s?string=',
            AgaviConfig::get('news.localize_api_url')
        );
        // create 'geoText' that we will query the localize api for.
        $queryValues = array(
            $location->getStreet(),
            $location->getHousenumber(),
            $location->getDetails(),
            $location->getCity(),
            $location->getPostalCode(),
            'Berlin'
        );
        foreach ($queryValues as $queryValue)
        {
            $queryValue = trim($queryValue);
            if (! empty($queryValue))
            {
                $url .= '+'.urlencode($queryValue);
            }
        }
        // prepare the api localize url
        
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
                __METHOD__ . " - Failed localizing item: " . $shofiItem->getIdentifier() .
                 PHP_EOL . "Error: " . curl_error($curl)
            );
            return FALSE;
        }
        else
        {
            // if everyting worked hydrate the location data et voila
            $localizeResults = json_decode($apiResponse, TRUE);
            if (0 < $localizeResults['items_count'])
            {
                $locationData = $localizeResults[0];
                // @todo: set street and housenumber if housenumber was inside the street field ...
                $data = array(
                    'district' => isset($locationData['district']) ? 
                        $locationData['district'] : NULL,
                    'administrativeDistrict' => isset($locationData['administrative district']) ? 
                        $locationData['administrative district'] : NULL,
                    'neighborhood' => isset($locationData['neighborhood']) ? $locationData['neighborhood'] : '',
                    'coordinates' => array(
                        'lon' => $locationData['longitude'],
                        'lat' => $locationData['latitude']
                    )
                );
                $location->applyValues($data);
            }
        }
        $this->logInfo("---------------------------------------");
        return TRUE;
    }

    protected function getWorkflowItemImplementor()
    {
        return self::ITEM_IMPLEMENTOR;
    }
}

?>
