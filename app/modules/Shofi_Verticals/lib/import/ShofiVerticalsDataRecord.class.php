<?php

/**
 * @version         $Id: ShofiVerticalsDataRecord.class.php -1   $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         Shofi_Verticals
 * @subpackage      Import
 */
abstract class ShofiVerticalsDataRecord extends BaseDataRecord
{
    const PROP_NAME = 'name';

    const PROP_TEASER = 'teaser';

    const PROP_URL = 'url';

    const PROP_IMAGES = 'images';

    protected $name;

    protected $teaser;

    protected $url;

    protected $images = array();

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
                self::PROP_NAME,
                self::PROP_TEASER,
            )
        );
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
        $this->images = $images;
    }
}

?>
