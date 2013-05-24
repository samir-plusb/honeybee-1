<?php

namespace Honeybee\Agavi\View;

use Honeybee\Core\Workflow\Plugin\InteractionResult;

class WorkflowSuccessView extends BaseView
{
    public function executeHtml(\AgaviRequestDataHolder $parameters)
    {
        return $this->getAttribute('content');
    }

    public function executeConsole(\AgaviRequestDataHolder $parameters)
    {
        return $this->getAttribute('content');
    }

    public function executeJson(\AgaviRequestDataHolder $parameters)
    {
        $result = $this->getAttribute('result');

        if ($result instanceof InteractionResult)
        {
            $this->getResponse()->setContent($this->getAttribute('content'));
        }
        else
        {
            $this->getResponse()->setContent(json_encode(array(
                'state' => 'ok',
                'messages' => array($result->getMessage()),
                'data' => array()
            )));
        }
    }
}
