<?php

namespace Honeybee\Agavi\View;

class EditSuccessView extends BaseView
{
    public function executeJson(\AgaviRequestDataHolder $requestData)
    {
        $document = $requestData->getParameter('document');
        $module_prefx = $this->getAttribute('module')->getOption('prefix');
        $routing = $this->getContext()->getRouting();

        $data = array(
            'state' => 'ok',
            'messages' => array($this->getContext()->getTranslationManager()->_('The document was saved successfully.', 'modules.labels')),
            'errors' => $this->getAttribute('errors', array()),
            'data' => $document->toArray()
        );
        
        if ('save_and_new' === $requestData->getParameter('save_type'))
        {
            $data['redirect_url'] = $routing->gen(sprintf('%s.edit', $module_prefx));
        }

        $this->getResponse()->setContent(json_encode($data));
    }
}
