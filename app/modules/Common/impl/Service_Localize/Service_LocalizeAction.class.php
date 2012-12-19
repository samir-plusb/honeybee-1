<?php

class Common_Service_LocalizeAction extends CommonBaseAction
{
    public function executeRead(AgaviRequestDataHolder $parameters) 
    {
        $queryValues = array(
            $parameters->getParameter('street', FALSE),
            $parameters->getParameter('housenumber', FALSE),
            $parameters->getParameter('city', 'Berlin'),
            $parameters->getParameter('postal_code', FALSE)
        );

        $coords = $this->fetchGeoCoordinates($queryValues);
        $this->setAttribute('location', $coords);

        return 'Success';
    }

    public function fetchGeoCoordinates(array $queryValues)
    {
        $url = sprintf('%s?string=', AgaviConfig::get('common.service.localization'));

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
}