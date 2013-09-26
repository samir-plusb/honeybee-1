<?php

namespace Honeybee\Agavi\View;

use Honeybee\Core\Workflow\Plugin\InteractionResult;

class CheckOutSuccessView extends BaseView
{
    public function executeHtml(\AgaviRequestDataHolder $parameters)
    {
        $this->setupHtml($parameters);
    }

    public function executeJson(\AgaviRequestDataHolder $parameters)
    {
        $document = $parameters->getParameter('document');
        $revision = $document->getRevision();
        $ticket = $document->getWorkflowTicket()->first();
        $owner = $ticket->getOwner();
        $this->getResponse()->setContent(
            json_encode(array(
                'state' => 'ok',
                'revision' => $revision,
                'owner' => !$owner ? '' : $owner
            ))
        );
    }
}
