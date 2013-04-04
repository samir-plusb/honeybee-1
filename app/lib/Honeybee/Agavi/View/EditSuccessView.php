<?php

namespace Honeybee\Agavi\View;

class EditSuccessView extends BaseView
{
    public function executeJson(\AgaviRequestDataHolder $requestData)
    {
        $document = $requestData->getParameter('document');
        
        $data = array(
            'state' => 'ok',
            'messages' => array($this->getContext()->getTranslationManager()->_('The document was saved successfully.', 'modules.labels')),
            'errors' => $this->getAttribute('errors', array()),
            'data' => array(
                'identifier' => $document->getIdentifier(),
                'revision' => $document->getRevision(),
                'shortId' => $document->getShortId()
            )
        );
        
        $this->getResponse()->setContent(json_encode($data));
    }
}
