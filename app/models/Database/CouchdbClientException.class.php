<?php

/**
 * The ExtendedCouchDbClient is a wrapper around php couchdb pecl library,
 * that extends the latter by composing it and adding in some missing functionality.
 *
 * @version         $Id: ExtendedCouchDbClient.class.php 460 2011-10-27 07:58:02Z tay $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Project
 * @subpackage      Database
 */
class CouchdbClientException extends Exception
{
    const UNPARSEABLE_RESPONSE = -1;
    const PUT_DATA = -2;

}