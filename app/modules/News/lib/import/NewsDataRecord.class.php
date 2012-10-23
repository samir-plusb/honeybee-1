<?php

/**
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <thorsten.schmitt-rink@berlinonline.de>
 * @package         News
 * @subpackage      Import
 */
abstract class NewsDataRecord extends BaseDataRecord implements INewsEntity
{
    /**
     * Holds the name of our title property.
     */
    const PROP_TITLE = 'title';

    /**
     * Holds the name of our category property.
     */
    const PROP_CONTENT = 'content';

    /**
     * Holds the name of our category property.
     */
    const PROP_CATEGORY = 'category';

    /**
     * Holds the name of our media property.
     */
    const PROP_MEDIA = 'media';

    /**
     * Holds the name of our geoData property.
     */
    const PROP_GEO = 'geoData';

    /**
     * Holds our title.
     *
     * @var         string
     */
    protected $title;

    /**
     * Holds our content.
     *
     * @var         string
     */
    protected $content;

    /**
     * Holds our category.
     *
     * @var         string
     */
    protected $category;

    /**
     * Holds our media (image, video and file assets for example).
     * The returned value is an array holding id's that can be used together with our ProjectAssetService
     * implementations.
     * Example return value structure:
     * -> array(23, 24, 512, 13);
     *
     * @var         array
     */
    protected $media;

    /**
     * Returns our geo data in the following structure:
     * -> array(
     *        'long' => $longValue,
     *        'lat'  => $latValue
     *    );
     *
     * @var         array
     */
    protected $geoData;

    /**
     * Return our title.
     *
     * @return      string
     *
     * @see         IDataRecord::getTitle()
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Return our content.
     *
     * @return      string
     *
     * @see         IDataRecord::getContent()
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Return our category.
     *
     * @return      string
     *
     * @see         IDataRecord::getCategory()
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Return our media.
     *
     * @return      string
     *
     * @see         IDataRecord::getMedia()
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * Return our geoData.
     *
     * @return      string
     *
     * @see         IDataRecord::getGeoData()
     */
    public function getGeoData()
    {
        return $this->geoData;
    }

    /**
     * Set our media.
     *
     * @param       array
     *
     * @see         IDataRecord::setMedia()
     */
    protected function setMedia(array $media)
    {
        $this->media = $media;
    }

    /**
     * Set our geoData.
     *
     * @param       array
     *
     * @see         IDataRecord::setGeoData()
     */
    protected function setGeoData(array $geoData)
    {
        $this->geoData = $geoData;
    }

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
                self::PROP_TITLE,
                self::PROP_CONTENT,
                self::PROP_CATEGORY,
                self::PROP_MEDIA,
                self::PROP_GEO
            )
        );
    }

    /**
     * Return an array holding the names of properties
     * that must be initialized before a record is considered as in a valid state.
     *
     * @return      array
     */
    protected function getRequiredProperties()
    {
        return array_merge(
            parent::getRequiredProperties(),
            array(
                self::PROP_TITLE,
                self::PROP_CONTENT,
                self::PROP_MEDIA,
                self::PROP_GEO
            )
        );
    }
}

?>
