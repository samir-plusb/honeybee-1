<?php

/**
 * The WorkflowItemDataImport is responseable for sending import notifications to the WorkflowSupervisor.
 *
 * @version         $Id: ProjectZendAclSecurityUser.class.php 412 2011-10-20 11:06:22Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         Import
 * @subpackage      Workflow
 */
class WorkflowItemDataImport extends BaseDataImport
{
    // ---------------------------------- <BaseDataImport OVERRIDES> -----------------------------

    /**
     * Creates a new CouchDbDataImport instance.
     *
     * @param       CouchDbDataImportConfig $config
     *
     * @throws      DataImportException If the given $config is no CouchDbDataImportConfig.
     *
     * @see         BaseDataImport::__construct()
     */
    public function __construct(IImportConfig $config)
    {
        if (!$config instanceof WorkflowItemDataImportConfig)
        {
            throw new DataImportException(sprintf(
                "Invalid config object given. Instance of WorkflowItemDataImportConfig expected, got: %s",
                get_class($config)
            ));
        }
        parent::__construct($config);
    }

    // ---------------------------------- </BaseDataImport OVERRIDES> ----------------------------


    // ---------------------------------- <BaseDataImport IMPL> ----------------------------------

    /**
     * Implementation of the BaseDataImport's importData strategy hook.
     * In this case we add the data to our buffer and then flush if necessary.
     *
     * @param       array $data
     *
     * @return      boolean
     *
     * @uses        CouchDbDataImport::flushImportBuffer()
     */
    protected function processRecord()
    {
        $record = $this->getCurrentRecord();
        $importData = $record->toArray();
        unset ($importData[ImportBaseDataRecord::PROP_IDENT]);

        try
        {
            /* @var $supervisor Workflow_SupervisorModel */
            $supervisor = AgaviContext::getInstance()->getModel('Supervisor', 'Workflow');
            $workflowItem = $supervisor->getItemPeer()->getItemByIdentifier($record->getIdentifier());

            if (! $workflowItem)
            {
                $this->createWorkflowItem($record->getIdentifier(), $importData);
            }
            else
            {
                $this->updateWorkflowItem($workflowItem, $importData);
            }
        }
        catch(Exception $e)
        {
            echo $e->getMessage() . PHP_EOL;
             // @TODO log exception and/or bubble to parent
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Create a new workflow item.
     *
     * @param array $importData
     */
    protected function createWorkflowItem($identifier, array $importData)
    {
        $supervisor = AgaviContext::getInstance()->getModel('Supervisor', 'Workflow');
        $workflowItem = new WorkflowItem(array(
            'identifier' => $identifier
        ));
        $workflowItem->createImportItem($importData);
        $supervisor->getItemPeer()->storeItem($workflowItem);
        if ($this->notifyEnabled())
        {
            try
            {
                $supervisor->onWorkflowItemCreated($workflowItem);
            }
            catch (Exception $e)
            {
                $supervisor->getItemPeer()->deleteItem($workflowItem);
                throw $e;
            }
        }
    }

    /**
     * Update an existing workflow item.
     *
     * @param array $importData
     */
    protected function updateWorkflowItem(IWorkflowItem $workflowItem, array $importData)
    {
        $supervisor = AgaviContext::getInstance()->getModel('Supervisor', 'Workflow');
        $workflowItem->updateImportItem($importData);
        $supervisor->getItemPeer()->storeItem($workflowItem);
        if ($this->notifyEnabled())
        {
            $supervisor->onWorkflowItemUpdated($workflowItem);
        }
    }

    protected function notifyEnabled()
    {
        return $this->config->getSetting(
            WorkflowItemDataImportConfig::CFG_NOTIFY_SUPERVISOR
        );
    }

    // ---------------------------------- </BaseDataImport IMPL> ---------------------------------
}

?>
