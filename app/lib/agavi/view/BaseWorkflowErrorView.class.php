<?php

class BaseWorkflowErrorView extends ProjectBaseView
{
    public function executeHtml(AgaviRequestDataHolder $parameters)
    {
        return $this->getAttribute('content');
    }

    public function executeText(AgaviRequestDataHolder $parameters) 
    {
        return $this->getAttribute('content');
    }

    public function executeJson(AgaviRequestDataHolder $parameters) 
    {
        return json_encode(
            array(
                'state' => 'error',
                'reason' => $this->getAttribute('reason'),
                'msg' => $this->getAttribute('content'),
                'errors' => $this->getAttribute('errors')
            )
        );
    }
}
