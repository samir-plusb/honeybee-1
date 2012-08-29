<?php

/**
 * The MoviesWorkflowSupervisor ovverides the general WorkflowSupervisor to hook in the correct database.
 *
 * @version         $Id: MoviesWorkflowSupervisor.class.php 1009 2012-03-02 19:01:55Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Movies
 * @subpackage      Workflow
 */
class MoviesWorkflowSupervisor extends WorkflowSupervisor
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
        $connections = AgaviConfig::get('movies.connections');
        return $connections['couchdb'];
    }
}

?>
