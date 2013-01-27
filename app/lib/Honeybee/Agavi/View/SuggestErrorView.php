<?php

namespace Honeybee\Agavi\View;

class SuggestErrorView extends BaseView
{
    /**
     * Handle presentation logic for json.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeJson(\AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $data = array(
            'state' => 'error',
            'errors' => $this->getAttribute('errors'),
            'data' => array()
        );

        $this->getResponse()->setContent(json_encode($data));
    }
}
