<?php

/**
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         Shofi
 */
interface IShofiEntity
{
    public function getCompany();

    public function getFirstName();

    public function getLastName();

    public function getTitle();

    public function getNamePrefix();

    public function getHistNamePrefix();

    public function getMiscNamePrefix();

    public function getPhone();

    public function getFax();

    public function getMobile();

    public function getEmail();

    public function getWeb();

    public function getLocation();
}

?>
