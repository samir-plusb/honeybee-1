<?php

/**
 * The WorkflowItemDataImport is responseable for sending import notifications to the WorkflowSupervisor.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         Import
 * @subpackage      Workflow
 */
abstract class WorkflowItemDataImport extends BaseDataImport
{
    /**
     * @var IWorkflowService
     */
    protected $workflowService;

    abstract protected function getWorkflowService();

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
    public function __construct(IConfig $config)
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

    protected function init(IDataSource $dataSource)
    {
        parent::init($dataSource);

        $this->workflowService = $this->getWorkflowService();
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
        unset ($importData[BaseDataRecord::PROP_IDENT]);

        $workflowItem = $this->workflowService->fetchWorkflowItemById($record->getIdentifier());
        if (! $workflowItem)
        {
            $this->createWorkflowItem($record->getIdentifier(), $importData);
        }
        else
        {
            $this->updateWorkflowItem($workflowItem, $importData);
        }
        return TRUE;
    }

    /**
     * Create a new workflow item.
     *
     * @param array $importData
     */
    protected function createWorkflowItem($identifier, array $importData, array $itemData = array())
    {
        $itemData['identifier'] = $identifier;
        $workflowItem = $this->workflowService->createWorkflowItem($itemData);
        $workflowItem->setMasterRecord(
            $workflowItem->createMasterRecord($importData)
        );

        if ($this->notifyEnabled())
        {
            try
            {
                $this->workflowService->notifyWorkflowItemCreated($workflowItem);
            }
            catch (Exception $e)
            {
                $this->workflowService->deleteWorkflowItem($workflowItem, TRUE);
                throw $e;
            }
        }
        return $workflowItem;
    }

    /**
     * Update an existing workflow item.
     *
     * @param array $importData
     */
    protected function updateWorkflowItem(IWorkflowItem $workflowItem, array $importData)
    {
        $workflowItem->updateMasterRecord($importData);
        $this->workflowService->storeWorkflowItem($workflowItem);
        if ($this->notifyEnabled())
        {
            $this->workflowService->notifyMasterRecordUpdated($workflowItem);
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
