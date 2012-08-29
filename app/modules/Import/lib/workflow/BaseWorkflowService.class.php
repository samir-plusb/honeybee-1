<?php

/**
 * The BaseWorkflowService serves as the base implementation of the IWorkflowService interface.
 *
 * @version         $Id: WorkflowItemDataImport.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         Import
 * @subpackage      Workflow
 */
abstract class BaseWorkflowService implements IWorkflowService
{
    protected $workflowSupervisor;

    abstract protected function getWorkflowItemImplementor();

    public function __construct($supervisorType)
    {
        $this->workflowSupervisor = WorkflowSupervisorFactory::createByTypeKey($supervisorType);
    }

    public function getWorkflowSupervisor()
    {
        return $this->workflowSupervisor;
    }

    public function notifyWorkflowItemCreated(IWorkflowItem $item)
    {
        $this->verifyWorkflowItemType($item);
        $this->workflowSupervisor->onWorkflowItemCreated($item);
    }

    public function notifyMasterRecordUpdated(IWorkflowItem $item)
    {
        $this->verifyWorkflowItemType($item);
        $this->workflowSupervisor->onWorkflowItemUpdated($item);
    }

    public function storeWorkflowItem(IWorkflowItem $item)
    {
        $this->verifyWorkflowItemType($item);
        if (! $this->workflowSupervisor->getWorkflowItemStore()->save($item))
        {
            return FALSE;
        }

        if (! ($ticketId = $item->getTicketId()))
        {
            $this->workflowSupervisor->onWorkflowItemCreated($item);
        }
        return TRUE;
    }

    public function deleteWorkflowItem(IWorkflowItem $item, $remove = FALSE)
    {
        $this->verifyWorkflowItemType($item);
        if ($remove)
        {
            return $this->workflowSupervisor->getWorkflowItemStore()->delete($item);
        }
        $item->setAttribute('marked_deleted', TRUE);
        return $this->workflowSupervisor->getWorkflowItemStore()->save($item);
    }

    public function fetchWorkflowItemById($identifier)
    {
        $item = $this->workflowSupervisor->getWorkflowItemStore()->fetchByIdentifier($identifier);
        if (! $item)
        {
            return NULL;
        }
        $this->verifyWorkflowItemType($item);
        return $item;
    }

    public function createWorkflowItem(array $data = array())
    {
        $data['type'] = $this->getWorkflowItemImplementor();
        return WorkflowItemStore::factory($data);
    }

    public function getTicketForItem(IWorkflowItem $item)
    {
        $ticketStore = $workflowService->getWorkflowSupervisor()->getWorkflowTicketStore();
        return $ticketStore->fetchByIdentifier(
            $workflowItem->getTicketId()
        );
    }

    protected function verifyWorkflowItemType(IWorkflowItem $item)
    {
        $expectedClass = $this->getWorkflowItemImplementor();
        if (! ($item instanceof $expectedClass))
        {
            throw new InvalidArgumentException(
                "The " . get_class($this) . " only supports workflow items by the type of " . $expectedClass
            );
        }
    }

    protected function logInfo($msg)
    {
        $logger = AgaviContext::getInstance()->getLoggerManager()->getLogger('app');
        $infoMsg = sprintf("[%s] %s", get_class($this), $msg);
        $logger->log(
            new AgaviLoggerMessage($infoMsg, AgaviLogger::INFO)
        );
    }
}

?>
