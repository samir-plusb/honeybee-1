<?php

/**
 * @version         $Id: ShofiDataRecord.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         Shofi
 * @subpackage      Import
 */
abstract class ShofiDataRecord extends BaseDataRecord implements IShofiEntity
{
    const PROP_COMPANY = 'company';

    const PROP_FIRST_NAME = 'firstName';

    const PROP_LAST_NAME = 'lastName';

    const PROP_TITLE = 'title';

    const PROP_NAME_PREF = 'namePrefix';

    const PROP_HIST_NAME_PREF = 'histNamePrefix';

    const PROP_MISC_NAME_PREF = 'miscNamePrefix';

    const PROP_LOCATION = 'location';

    const PROP_PHONE = 'phone';

    const PROP_FAX = 'fax';

    const PROP_MOBILE = 'mobile';

    const PROP_EMAIL = 'email';

    const PROP_WEB = 'web';

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

    /**
     * Return an array holding property names of properties,
     * which we want to expose through our IDataRecord::toArray() method.
     *
     * @return      array
     */
    protected function getExposedProperties()
    {
        return array_merge(
            parent::getExposedProperties(),
            array(
                self::PROP_COMPANY,
                self::PROP_FIRST_NAME,
                self::PROP_LAST_NAME,
                self::PROP_TITLE,
                self::PROP_LOCATION,
                self::PROP_NAME_PREF,
                self::PROP_HIST_NAME_PREF,
                self::PROP_MISC_NAME_PREF,
                self::PROP_PHONE,
                self::PROP_FAX,
                self::PROP_MOBILE,
                self::PROP_EMAIL,
                self::PROP_WEB
            )
        );
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
