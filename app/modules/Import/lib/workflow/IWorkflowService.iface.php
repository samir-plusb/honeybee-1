<?php

/**
 * The IWorkflowService defines the behaviour expected to be exposed by workflow services.
 *
 * @version         $Id: WorkflowItemDataImport.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         Import
 * @subpackage      Workflow
 */
interface IWorkflowService
{
    public function notifyWorkflowItemCreated(IWorkflowItem $item);

    public function notifyMasterRecordUpdated(IWorkflowItem $item);

    public function storeWorkflowItem(IWorkflowItem $item);

    public function deleteWorkflowItem(IWorkflowItem $item, $remove = FALSE);

    public function fetchWorkflowItemById($identifier);

    public function createWorkflowItem(array $data);
}

?>
