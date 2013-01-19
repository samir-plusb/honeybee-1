<?php

class BaseWorkflowSuccessView extends ProjectBaseView
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
        $this->getResponse()->setContent($this->getAttribute('content'));
    }
}
