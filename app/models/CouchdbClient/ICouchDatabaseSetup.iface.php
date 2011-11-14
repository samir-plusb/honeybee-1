<?php

/**
 * The ICouchDatabaseSetup interface is responseable for setting up couchdb for usage. eg. define design documents
 *
 * @package         Database
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Tom Anheyer
 */
interface ICouchDatabaseSetup
{
    /**
     * Setup everything required to provide the functionality exposed by our module.
     * In this case setup a couchdb database and view for our asset idsequence.
     *
     * @param       boolean $tearDownFirst
     */
    public function setup($tearDownFirst = FALSE);
}