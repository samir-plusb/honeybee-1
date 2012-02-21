<?php

/**
 * Exception class for ExtendedCouchDbClient exceptions
 *
 * Postive exception codes are CURL error codes.
 *
 * @see ExtendedCouchDbClient
 *
 * @version         $Id: ExtendedCouchDbClient.class.php 460 2011-10-27 07:58:02Z tay $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          tay
 * @package Project
 * @subpackage Database
 */
class CouchdbClientException extends Exception
{
    /**
     * response from couch db server is not parseable
     */
    const UNPARSEABLE_RESPONSE = -1;

    /**
     * prepararing data for PUT requests failed
     */
    const PUT_DATA = -2;

}