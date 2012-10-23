<?php

/**
 * The IShofiCoreItem interface defines the structure exposed by the shofi sales context.
 *
 * @version $Id$
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Shofi
 * @subpackage Workflow/Item
 */
interface IShofiCoreItem extends IDataObject
{
    public function getCompany();

    public function setCompany($company);

    public function getFirstName();

    public function setFirstName($firstName);

    public function getLastName();

    public function setLastName($lastName);

    public function getName();

    public function getPhone();

    public function setPhone($phone);

    public function getFax();

    public function setFax($fax);

    public function getMobile();

    public function setMobile($mobile);

    public function getEmail();

    public function setEmail($email);

    public function getLocation();

    public function setLocation($location);
}

?>
