<?php

/**
 * The ShofiCategoriesWorkflowSupervisor ovverides the general WorkflowSupervisor to hook in the correct database.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Shofi_Categories
 * @subpackage      Workflow
 */
class ShofiCategoriesWorkflowSupervisor extends WorkflowSupervisor
{
    private static $instance;

    /**
     * get a singleton instance for this model
     *
     * @return WorkflowSupervisor
     */
    public static function getInstance()
    {
        if (! self::$instance)
        {
            self::$instance = new self();
            self::$instance->initialize();
        }
        return self::$instance;
    }

    public static function getCouchDbDatabasename()
    {
        $connections = AgaviConfig::get('shofi_categories.connections');
        return $connections['couchdb'];
    }
}

?>
