<?php

namespace Honeybee\Agavi\Action;

use Dat0r\Core\Document;
use Dat0r\Core\Error;
use Honeybee\Core\Workflow\Plugin;

class CheckOutAction extends BaseAction
{
    public function execute(\AgaviRequestDataHolder $request_data)
    {
        $module = $this->getModule();
        $service = $module->getService();
        $user = $this->getContext()->getUser();

        $document = $request_data->getParameter('document');
        $workflow_ticket = $document->getWorkflowTicket()->first();
        $username = $user->getAttribute('login');
        $role = $user->getAttribute('acl_role');
        if ('honeybee-editor' === $role || $username === $workflow_ticket->getOwner()) {
            $workflow_ticket->setOwner(null);
            try {
                $service->save($document);
                return 'Success';
            } catch (Conflict $conflict) {
                return 'Error';
            }
        } else {
            return 'Error';
        }
    }
}
