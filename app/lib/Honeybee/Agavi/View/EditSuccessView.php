<?php

namespace Honeybee\Agavi\View;

class EditSuccessView extends BaseView
{
    public function executeJson(\AgaviRequestDataHolder $requestData)
    {
        $document = $requestData->getParameter('document');
        
        $data = array(
            'state' => 'ok',
            'messages' => array('Das Dokument wurde erfolgreich gespeichert.'),
            'errors' => $this->getAttribute('errors', array()),
            'data' => array(
                'identifier' => $document->getIdentifier(),
                'revision' => $document->getRevision()
            )
        );
        
        $this->getResponse()->setContent(json_encode($data));
    }
}
