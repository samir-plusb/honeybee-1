<?php

/**
 * The FrontendApiClient is responseable for issuing requests against the current 'local-news' frontend.
 * These requests are sent in order to keep the frontend in sync with the published/deleted items of the backend.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         News
 * @subpackage      Api
 */
class FrontendApiClient
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    /**
     * Holds the name of the config setting used to retrieve the correct frontend api url to use for the current env.
     */
    const SETTING_BASE_URL = 'items.frontend_api_url';

    /**
     * Holds the url suffix used to build the api url, that we send our delete requests to.
     */
    const URL_SUFFIX_DELETE = 'delete';

    /**
     * Holds the url suffix used to build the api url, that we send our update requests to.
     */
    const URL_SUFFIX_UPDATE = 'import';

    /**
     * Represents the (htto) status code expected for api requests that were successfully processed.
     */
    const STATUS_CODE_OK = 200;

    // ---------------------------------- </CONSTANTS> -------------------------------------------


    // ---------------------------------- <PUBLIC METHODS> ---------------------------------------

    /**
     * Sends an 'update' request to the frontend api, enclosing the complete workflow item's data.
     *
     * @param IWorkflowItem $workflowItem
     */
    public function updateWorkflowItem(IWorkflowItem $workflowItem)
    {
        $this->sendApiRequest($this->buildUpdateItemUrl(), $workflowItem->toArray());
    }

    /**
     * Sends an 'delete' request to the frontend api, enclosing only the id's of the workflow item's content items.
     *
     * @param IWorkflowItem $workflowItem
     */
    public function deleteWorkflowItem(IWorkflowItem $workflowItem)
    {
        $apiData = array(
            'ContentItems' => array()
        );
        foreach ($workflowItem->getContentItems() as $contentItem)
        {
            $apiData['ContentItems'][] = $contentItem->getIdentifier();
        }
        $this->sendApiRequest($this->buildDeleteItemUrl(), $apiData);
    }

    // ---------------------------------- </PUBLIC METHODS> --------------------------------------


    // ---------------------------------- <WORKING METHODS> --------------------------------------

    /**
     * Send a (json)request to the given api url enclosing the given data.
     *
     * @param string $url
     * @param array $data
     *
     * @throws FrontendApiClientException If something other than the expected success occurs.
     */
    protected function sendApiRequest($url, array $data)
    {
        $curlHandle = ProjectCurl::create();

        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_POST, TRUE);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=UTF-8',
            'Accept: application/json; charset=UTF-8',
            'X-Requested-With: XMLHttpRequest'
        ));

        $response = curl_exec($curlHandle);
        $respCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);

        if (self::STATUS_CODE_OK !== $respCode)
        {
            $err = curl_error($curlHandle);
            $message = "An unexpected error occured while trying to send an api call to the frontend at url: " . $url .
                "The frontend returned status-code: '" . $respCode . "' and the following output: " . $response;
            if ($err)
            {
                $message .= "The original curl error was: " . $err;
            }
            throw new FrontendApiClientException($message);
        }
    }

    /**
     * Helper method that build the correct url for sending update requests to the frontend.
     *
     * @return string
     */
    protected function buildUpdateItemUrl()
    {
        $baseUrl = AgaviConfig::get(self::SETTING_BASE_URL);
        return $baseUrl . self::URL_SUFFIX_UPDATE;
    }

    /**
     * Helper method that build the correct url for sending delete requests to the frontend.
     *
     * @return string
     */
    protected function buildDeleteItemUrl()
    {
        $baseUrl = AgaviConfig::get(self::SETTING_BASE_URL);
        return $baseUrl . self::URL_SUFFIX_DELETE;
    }

    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>
