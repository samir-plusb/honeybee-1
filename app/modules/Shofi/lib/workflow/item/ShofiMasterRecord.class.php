<?php

/**
 * The ShofiMasterRecord is a data object implementation of the IShofiEntity interface.
 * It holds the originally imported unmodified data as provided by the shofi import
 * and is used as the primary datasource for the shofi editing process.
 *
 * @version $Id: ShofiMasterRecord.class.php -1   $
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Shofi
 * @subpackage Workflow/Item
 */
class ShofiMasterRecord extends MasterRecord implements IShofiEntity
{
    protected $company;

    protected $firstName;

    protected $lastName;

    protected $title;

    protected $namePrefix;

    protected $histNamePrefix;

    protected $miscNamePrefix;

    protected $location;

    protected $phone;

    protected $fax;

    protected $mobile;

    protected $email;

    protected $web;

    public static function fromArray(array $data = array())
    {
        return new self($data);
    }

    public function getCompany()
    {
        return $this->company;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getNamePrefix()
    {
        return $this->namePrefix;
    }

    public function getHistNamePrefix()
    {
        return $this->histNamePrefix;
    }

    public function getMiscNamePrefix()
    {
        return $this->miscNamePrefix;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function getFax()
    {
        return $this->fax;
    }

    public function getMobile()
    {
        return $this->mobile;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getWeb()
    {
        return $this->web;
    }

    public function getLocation()
    {
        return $this->location;
    }
}

?>
