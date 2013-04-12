<?php

namespace Honeybee\Agavi\Action;

class SuggestAction extends BaseAction
{
    public function executeRead(\AgaviRequestDataHolder $requestData)
    {
        $displayField = $requestData->getParameter('display_field');
        $identityField = $requestData->getParameter('identity_field');
        $term = $requestData->getParameter('term');

        $module = $this->getModule();
        $service = $module->getService();
        $result = NULL;

        if ($term)
        {
            $result = $service->suggestDocuments($term, $displayField);
        }
        else
        {
            $result = $service->fetchAll(0, 20);
        }

        $suggestData = array();
        foreach ($result['documents'] as $document)
        {
            $suggestData[] = array(
                $displayField => $document->getValue($displayField),
                $identityField => $document->getValue($identityField)
            );
        }

        $this->setAttribute('data', $suggestData);

        return 'Success';
    }

    /**
     * Handles validation errors that occur for any our derivates.
     *
     * @param AgaviRequestDataHolder $parameters
     *
     * @return string The name of the view to invoke.
     */
    public function handleReadError(\AgaviRequestDataHolder $parameters)
    {
        $errors = array();
        foreach ($this->getContainer()->getValidationManager()->getErrorMessages() as $errMsg)
        {
            $errors[]= $errMsg['message'];
        }
        $this->setAttribute('errors', $errors);

        return 'Error';
    }
}
