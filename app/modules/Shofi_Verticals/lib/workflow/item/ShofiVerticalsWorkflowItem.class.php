<?php

/**
 * The ShofiVerticalsWorkflowItem extends the WorkflowItem and serves as the aggregate root for all aggregated shofi-vertical data objects.
 *
 * @version $Id: ShofiVerticalsWorkflowItem.class.php -1   $
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Shofi_Verticals
 * @subpackage Workflow/Item
 */
class ShofiVerticalsWorkflowItem extends WorkflowItem
{
    public static function fromArray(array $data = array())
    {
        return new self($data);
    }

    public function determineWorkflow()
    {
        return 'shofi_verticals';
    }

    protected function getMasterRecordImplementor()
    {
        return "ShofiVerticalsMasterRecord";
    }
}

?>
