<?php

/**
 * The ShofiCategoriesWorkflowItem extends the WorkflowItem and serves as the aggregate root for all aggregated shofi-category data objects.
 *
 * @version $Id: ShofiWorkflowItem.class.php -1   $
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Shofi_Categories
 * @subpackage Workflow/Item
 */
class ShofiCategoriesWorkflowItem extends WorkflowItem
{
    public static function fromArray(array $data = array())
    {
        return new self($data);
    }

    public function determineWorkflow()
    {
        return 'shofi_categories';
    }

    protected function getMasterRecordImplementor()
    {
        return "ShofiCategoriesMasterRecord";
    }
}

?>
