<?php

/**
 * The IDatabaseSetup interface is responseable for setting up databases for usage.
 *
 * @package         Database
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Tom Anheyer
 */
interface IDatabaseSetup
{
    /**
     * Setup everything required to provide the functionality exposed by our module.
     * In this case setup a couchdb database and view for our asset idsequence.
     *
     * @param       boolean $tearDownFirst
     */
    public function setup($tearDownFirst = FALSE);
}