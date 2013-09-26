<?php

namespace Honeybee\Agavi\Action;

use Honeybee\Core\Storage\CouchDb\Conflict;

class CheckInAction extends BaseAction
{
    public function execute(\AgaviRequestDataHolder $request_data)
    {
        $document = $request_data->getParameter('document');
        $revision = $request_data->getParameter('rev');

        $module = $this->getModule();
        $service = $module->getService();

        $workflow_ticket = $document->getWorkflowTicket()->first();
        $current_editor = $workflow_ticket->getOwner();
        $user = $this->getContext()->getUser();
        $username = $user->getAttribute('login');

        if ($username === $current_editor) {
            // current user already is registered as editor
            return 'Success';
        } elseif (!empty($current_editor) && $current_editor !== 'nobody') {
            // someone else is currently editing the document
            error_log("// someone else is currently editing the document: " . $current_editor);
            return 'Error';
        } elseif ($revision === $document->getRevision()) {
            // the document is available and the revisions match, lets check in
            $workflow_ticket->setOwner($username);
            try {
                $service->save($document);
                return 'Success';
            } catch (Conflict $conflict) {
                error_log("exception caught!");
                return 'Error';
            }
        } else {
            // revision don't match, can't check in
            error_log("// revision don't match, can't check in");
            return 'Error';
        }
    }
}
