<?php

namespace Honeybee\Agavi\View;

use Honeybee\Core\Workflow\Plugin\InteractionResult;

class WorkflowErrorView extends BaseView
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
            $this->getResponse()->setContent(
                json_encode(array(
                    'state' => 'error',
                    'reason' => $this->getAttribute('reason'),
                    'msg' => $this->getAttribute('content'),
                    'errors' => $this->getAttribute('errors')
                ))
            );
        }
    }
}