<?php

namespace Honeybee\Agavi\Action;

use Honeybee\Core\Finder\ElasticSearch;
use Elastica;

class SuggestAction extends BaseAction
{
    public function executeRead(\AgaviRequestDataHolder $request_data)
    {
        $display_field = $request_data->getParameter('display_field');
        $identity_field = $request_data->getParameter('identity_field');
        $term = $request_data->getParameter('term');

        $module = $this->getModule();
        $repository = $module->getRepository();

        $data = array();
        $query = null;
        if ($term) {
            $query_builder = new ElasticSearch\SuggestQueryBuilder();
            $query = $query_builder->build(
                array('term' => $term, 'field' => $display_field, 'sorting' => array())
            );
        }

        $result = $repository->getFinder()->find($query, 10, 0);
        $suggest_data = array();
        foreach ($result['data'] as $document_data) {
            $suggest_data[] = array(
                $display_field => $document_data[$display_field],
                $identity_field => $document_data[$identity_field]
            );
        }

        $this->setAttribute('data', $suggest_data);

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
        foreach ($this->getContainer()->getValidationManager()->getErrorMessages() as $errMsg) {
            $errors[]= $errMsg['message'];
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
