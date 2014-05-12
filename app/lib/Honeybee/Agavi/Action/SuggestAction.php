<?php

namespace Honeybee\Agavi\Action;

use Honeybee\Core\Finder\ElasticSearch;
use Elastica;
use AgaviConfig;

class SuggestAction extends BaseAction
{
    public function executeRead(\AgaviRequestDataHolder $request_data)
    {
        $term = $request_data->getParameter('term');
        $display_field = $request_data->getParameter('display_field');

        $this->setAttribute(
            'data',
            $this->getModule()->getService()->suggestData(
                $term,
                $display_field
            )
        );

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

        foreach ($this->getContainer()->getValidationManager()->getErrorMessages() as $error_msg) {
            $errors[]= $error_msg['message'];
        }

        $this->setAttribute('errors', $errors);

        return 'Error';
    }

    public function getCredentials()
    {
        return sprintf(
            '%s::read',
            $this->getModule()->getOption('prefix')
        );
    }
}
