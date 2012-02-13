<?php

/**
 * The News_Api_ExtractLocationAction is repsonseable handling location extraction api requests.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         News
 * @subpackage      Mvc
 */
class News_Api_ExtractLocationAction extends NewsBaseAction
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
        $geoText = $parameters->getParameter('geo_text', '');
        $url = sprintf('%s?string=%s', AgaviConfig::get('news_workflow.localize_api'), urlencode($geoText));
        $curl = ProjectCurl::create();
        curl_setopt($curl, CURLOPT_URL, $url);
        $resp = curl_exec($curl);

        $this->logInfo(
            "Received following response for localization of '" . $url . "': " . PHP_EOL . $resp
        );

        if (($error = curl_error($curl)))
        {
            $this->setAttribute('location', array('items_count' => 0));
        }
        else
        {
            $this->setAttribute('location', json_decode($resp, TRUE));
        }
        return 'Success';
    }
}

?>