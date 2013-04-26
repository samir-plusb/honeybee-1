<?php

use Guzzle\Http\Client;

class Common_Service_LocalizeAction extends CommonBaseAction
{
    public function executeRead(AgaviRequestDataHolder $parameters) 
    {
        $queryValues = array(
            'street' => $parameters->getParameter('street', FALSE),
            'house' => $parameters->getParameter('housenumber', FALSE),
            'city' => $parameters->getParameter('city', 'Berlin'),
            'postal' => $parameters->getParameter('postal_code', FALSE)
        );

        $coords = $this->fetchGeoCoordinates($queryValues);
        $this->setAttribute('location', $coords);

        return 'Success';
    }

    public function fetchGeoCoordinates(array $queryValues)
    {
        $client = new Client(\AgaviConfig::get('common.geo_service.url'));
        $auth = \AgaviConfig::get('common.geo_service.auth');

        $request = $client->get()->setAuth(
            $auth['user'], $auth['pwd']
        );
        $request->getQuery()->merge($queryValues);
        $response = $request->send();

        if (200 > $response->getStatusCode() || 300 <= $response->getStatusCode())
        {
            return FALSE;
        }

        $this->logInfo("Received following response for localization of '" . $request->getUrl() . "': " . PHP_EOL . $response->getBody());
        
        $location = array('item_count' => 0);
    
        $jdata = $response->json();
        if (!is_array($jdata))
        {
            $this->logInfo("'$url': Broken JSON response");
            return $location;
        }
    
        if (empty($jdata['location']['wgs84']['lon']) || empty($jdata['location']['wgs84']['lat']))
        {
            return $location;
        }
    
        if (isset($jdata['location']['accuracy']) && $jdata['location']['accuracy'] > 0)
        {
            $location = array(
                    $this->mapResponse($jdata)
            );
            if (!empty($jdata['alternatives']))
            {
                foreach ($jdata['alternatives'] as $alt)
                {
                    $location[] = $this->mapResponse($alt);
                }
            }
            $location['items_count'] = count($location);
        }
    
        return $location;
    }

    protected function mapResponse(array $jdata)
    {
        return array(
            "street_name" => $jdata['address']['street'],
            "number" => $jdata['address']['house'],
            "uzip" => $jdata['address']['postal-code'],
            "city" => $jdata['address']['municipality'],
            "latitude" => $jdata['location']['wgs84']['lat'],
            "longitude" => $jdata['location']['wgs84']['lon'],
            "street" => $jdata['address']['street'] . ' ' . $jdata['address']['house'] . $jdata['address']['houseext'],
            "source" => isset($jdata['meta']) ? $jdata['meta']['source'] : NULL,
            "neighborhood" => $jdata['address']['urban-subdivision'],
            "administrative district" => $jdata['address']['administrative-district'],
            "district" => $jdata['address']['district']
        );
    }
}
