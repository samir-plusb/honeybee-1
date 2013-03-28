<?php

use \Honeybee\Core\Util\Http\CurlFactory;

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
        $url = \AgaviConfig::get('common.geo_service.url') . '?' . http_build_query($queryValues);
    
        $curl = CurlFactory::create();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERPWD, \AgaviConfig::get('common.geo_service.auth'));
    
        $resp = curl_exec($curl);    
        $this->logInfo("Received following response for localization of '" . $url . "': " . PHP_EOL . $resp);
        
        $location = array('item_count' => 0);
    
        if (200 != curl_getinfo($curl, CURLINFO_HTTP_CODE))
        {
            $this->logInfo("'$url' failed with: " . curl_error($curl));
            curl_close($curl);
            return $location;
        }
    
        curl_close($curl);
        $jdata = json_decode($resp, TRUE);
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
