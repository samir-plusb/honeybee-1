<?php

namespace Honeybee\Agavi\View;

class SuggestSuccessView extends BaseView
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
            'state' => 'ok',
            'messages' => array(),
            'data' => $this->getAttribute('data')
        );
        
        $this->getResponse()->setContent(json_encode($data));
    }
}
