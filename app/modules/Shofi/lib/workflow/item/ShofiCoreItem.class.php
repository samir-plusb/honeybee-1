<?php

/**
 * The ShofiCoreItem holds the common data for shofi locations.
 *
 * @version $Id: ShofiWorkflowItem.class.php -1   $
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Shofi
 * @subpackage Workflow/Item
 */
class ShofiCoreItem extends BaseDataObject implements IShofiCoreItem
{
    protected $company;

    protected $title;

    protected $firstName;

    protected $lastName;

    protected $name;

    protected $phone;

    protected $fax;

    protected $mobile;

    protected $email;

    protected $website;

    protected $location;

    public static function fromArray(array $data = array())
    {
        return new self($data);
    }

    public function __construct(array $data = array())
    {
        parent::__construct($data);
    }

    public function toArray()
    {
        if (empty($this->name))
        {
            $this->name = $this->buildName();
        }
        
        return parent::toArray();
    }

    public function getCompany()
    {
        return $this->company;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setCompany($company)
    {
        $this->company = $company;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    public function getFax()
    {
        return $this->fax;
    }

    public function setFax($fax)
    {
        $this->fax = $fax;
    }

    public function getMobile()
    {
        return $this->mobile;
    }

    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getWebsite()
    {
        return $this->website;
    }

    public function setWebsite($website)
    {
        $this->website = $website;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function setLocation($location)
    {
        if ($location instanceof IItemLocation)
        {
            $this->location = $location;
        }
        else if (is_array($location))
        {
            $this->location = ItemLocation::fromArray($location);
        }
    }

    protected function buildName()
    {
        //case $coreItem.hasTag('arzt'):
        //return sprintf("%s %s %s", $this->title, $this->firstName, $this->lastName);
        if (! empty($this->company))
        {
            return $this->company;
        }
        if (! empty($this->title))
        {
            return sprintf("%s %s %s", $this->title, $this->firstName, $this->lastName);
        }
        return sprintf("%s %s", $this->firstName, $this->lastName);
    }
}

?>
