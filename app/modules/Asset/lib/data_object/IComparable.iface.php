<?php

/**
 * The IComparable interface provides a contract that objects cann fullfill to compare each other.
 *
 * @version         $Id: IComparable.iface.php 1299 2012-06-12 16:09:14Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Project
 * @subpackage      DataObject
 */
interface IComparable
{
    /**
     * Return -1 if smaller, 0 if equal and 1 if bigger.
     *
     * @return int
     */
    public function compareTo($other);
}

?>