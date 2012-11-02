<?php

/**
 * The IDatabaseSetup interface is responseable for setting up databases for usage.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Tom Anheyer
 * @package Project
 * @subpackage Agavi/Database
 */
interface IDatabaseSetup
{
    /**
     * Setup everything required to provide the functionality exposed by our module.
     * In this case setup a couchdb database and view for our asset idsequence.
     *
     * @param       boolean $tearDownFirst
     */
    public function execute(AgaviDatabase $database, $tearDownFirst = FALSE);
}
