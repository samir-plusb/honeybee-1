<?php

/**
 * The NewsWorkflowSupervisor ovverides the general WorkflowSupervisor to hook in the correct database.
 *
 * @version         $Id: NewsWorkflowSupervisor.class.php 1009 2012-03-02 19:01:55Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         News
 */
class NewsWorkflowSupervisor extends WorkflowSupervisor
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
        $connections = AgaviConfig::get('news.connections');
        return $connections['couchdb'];
    }
}

?>
