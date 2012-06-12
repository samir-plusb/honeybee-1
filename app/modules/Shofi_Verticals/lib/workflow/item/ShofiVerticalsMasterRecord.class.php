<?php

/**
 * The ShofiVerticalsMasterRecord holds the main data of a ShofiCategory.
 *
 * @version $Id: ShofiVerticalsMasterRecord.class.php -1   $
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @author Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package Shofi_Verticals
 * @subpackage Workflow/Item
 */
class ShofiVerticalsMasterRecord extends MasterRecord
{
    protected $name;

    protected $teaser;

    protected $url;

    protected $images = array();

    protected $categories = array();

    public static function fromArray(array $data = array())
    {
        return new self($data);
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getTeaser()
    {
        return $this->teaser;
    }

    public function setTeaser($teaser)
    {
        $this->teaser = $teaser;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getImages()
    {
        return $this->images;
    }

    public function setImages(array $images)
    {
        $assets = array();
        foreach ($images as $assetId)
        {
            if (is_numeric($assetId))
            {
                $assets[] = (int)$assetId;
            }
        }
        $this->images = $assets;
    }

    public function getCategories()
    {
        return $this->categories;
    }

    public function setCategories(array $categories)
    {
        $this->categories = $categories;
    }
}

?>
