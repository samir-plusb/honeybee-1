<?php

class FrontendApiClient
{
    const SETTING_BASE_URL = 'items.frontend_api_url';

    const URL_SUFFIX_DELETE = 'delete';

    const URL_SUFFIX_UPDATE = 'import';

    const STATUS_CODE_OK = 200;

    public function updateWorkflowItem(IWorkflowItem $workflowItem)
    {
        $this->sendApiRequest($this->buildUpdateItemUrl(), $workflowItem->toArray());
    }

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

        $this->logInfo(sprintf(
            "[%s] Trying to send api-request to url: %s with the following data:\n",
            get_class($this),
            $url,
            print_r($data, TRUE)
        ));

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

        $this->logInfo("[" . get_class($this) . "] Successfully sent api request to url: " . $url);
    }

    protected function buildUpdateItemUrl()
    {
        $baseUrl = AgaviConfig::get(self::SETTING_BASE_URL);
        return $baseUrl . self::URL_SUFFIX_UPDATE;
    }

    protected function buildDeleteItemUrl()
    {
        $baseUrl = AgaviConfig::get(self::SETTING_BASE_URL);
        return $baseUrl . self::URL_SUFFIX_DELETE;
    }

    protected function logInfo($msg)
    {
        $ctx = AgaviContext::getInstance();
        $logger = $ctx->getLoggerManager()->getLogger('app');
        $logger->log(
            new AgaviLoggerMessage(
                $msg,
                AgaviLogger::INFO
            )
        );
    }
}

?>
