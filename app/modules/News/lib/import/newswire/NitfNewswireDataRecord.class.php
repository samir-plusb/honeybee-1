<?php

/**
 * The NitfNewswireDataRecord class is a concrete implementation of the NewswireDataRecord base class.
 * It provides processing data in the nitf format.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Tom Anheyer <tom.anheyer@berlinonline.de>
 * @package         News
 * @subpackage      Import/Newswire
 */
class NitfNewswireDataRecord extends NewswireDataRecord
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    /**
     * Holds the name of our subtitle property.
     */
    const PROP_SUBTITLE = 'subtitle';

    /**
     * Holds the name of our abstract property.
     */
    const PROP_ABSTRACT = 'abstract';

    /**
     * Holds the name of our link.
     */
    const PROP_COPYRIGHT = 'copyright';

    /**
     * Holds the name of our keywords property.
     */
    const PROP_KEYWORDS = 'keywords';

    /**
     * Holds the name of our release date/time property.
     */
    const PROP_DATE_RELEASE = 'release';

    /**
     * Holds the name of our expire date/time property.
     */
    const PROP_DATE_EXPIRE = 'expire';

    /**
     * Holds the name of our table property.
     */
    const PROP_TABLE = 'table';

    // ---------------------------------- <CONSTANTS> --------------------------------------------


    // ---------------------------------- <MEMBERS> ----------------------------------------------

    /**
     * Holds our subtitle.
     *
     * @var         string
     */
    protected $subtitle;

    /**
     * Holds our abstract.
     *
     * @var         string
     */
    protected $abstract;

    /**
     * Holds our release.
     *
     * @var         string
     */
    protected $release;

    /**
     * Holds our expire.
     *
     * @var         string
     */
    protected $expire;

    /**
     * Holds our copyright.
     *
     * @var         string
     */
    protected $copyright;

    /**
     * Holds our keywords.
     *
     * @var         string
     */
    protected $keywords;

    /**
     * Holds our table.
     *
     * @var         string
     */
    protected $table;

    // ---------------------------------- </MEMBERS> ---------------------------------------------


    // ---------------------------------- <PUBLIC METHODS> ---------------------------------------

    /**
     * Returns our subtitle.
     *
     * @return      string
     */
    public function getSubtitle()
    {
        return $this->subtitle;
    }

    /**
     * Returns our abstract.
     *
     * @return      string
     */
    public function getAbstract()
    {
        return $this->abstract;
    }

    /**
     * Returns our release-date.
     *
     * @return      string A ISO8601 date string.
     */
    public function getRelease()
    {
        return $this->release;
    }

    /**
     * Returns our expire-date.
     *
     * @return      string A ISO8601 date string.
     */
    public function getExpire()
    {
        return $this->expire;
    }

    /**
     * Returns our copyright.
     *
     * @return      string
     */
    public function getCopyright()
    {
        return $this->copyright;
    }

    /**
     * Returns an array of keywords.
     *
     * @return      array
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Returns our table html string.
     *
     * @return      string
     */
    public function getTable()
    {
        return $this->table;
    }

    // ---------------------------------- </PUBLIC METHODS> --------------------------------------


    // ---------------------------------- <BaseDataRecord IMPL> -----------------------------------

    protected function parseData($data)
    {
        $parser = new NitfNewswireXmlParser();
        $parsedData = $parser->parseXml($data);
        $content = $parsedData['content'];
        if (! is_array($content))
        {
            $content = array($content);
        }
        $keywords = $parsedData['keywords'];
        if (! is_array($keywords))
        {
            $keywords = array($keywords);
        }
        return array(
            self::PROP_IDENT => $parsedData['doc-id'],
            self::PROP_TITLE => $parsedData['title'],
            self::PROP_COPYRIGHT => $parsedData['copyright'],
            self::PROP_TIMESTAMP => $parsedData['date-issue'],
            self::PROP_DATE_EXPIRE => $parsedData['date-expire'],
            self::PROP_DATE_RELEASE => $parsedData['date-release'],
            self::PROP_MEDIA => $this->importMedia($parsedData['media']),
            self::PROP_TABLE => $parsedData['table'],
            self::PROP_ABSTRACT => $parsedData['abstract'],
            self::PROP_CONTENT => join("\n\n", $content),
            self::PROP_KEYWORDS => $keywords,
            self::PROP_SUBTITLE => $parsedData['headline'],
            self::PROP_CATEGORY => $parsedData['fixture-id'],
            self::PROP_GEO => array()
        );
    }

    // ---------------------------------- </BaseDataRecord IMPL> ----------------------------------


    // ---------------------------------- <NewsDataRecord OVERRIDES> -----------------------------

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
                self::PROP_SUBTITLE,
                self::PROP_ABSTRACT,
                self::PROP_KEYWORDS,
                self::PROP_COPYRIGHT,
                self::PROP_DATE_RELEASE,
                self::PROP_DATE_EXPIRE,
                self::PROP_TABLE
            )
        );
    }

    /**
     * Set our keywords during hydrate.
     *
     * @param       array $keywords
     */
    protected function setKeywords(array $keywords)
    {
        $this->keywords = $keywords;
    }

    // ---------------------------------- </NewsDataRecord OVERRIDES> ----------------------


    // ---------------------------------- <WORKING METHODS> --------------------------------------

    /**
     * import image media objects
     *
     * @param       array $media
     *
     * @return      array with reference information
     */
    protected function importMedia(array $media)
    {
        $images = array();
        foreach ($media as $curImage)
        {
            $relSrc = realpath(
                dirname($this->getOrigin()) . DIRECTORY_SEPARATOR . $curImage['source']
            );
            if ($relSrc)
            {
                unset($curImage['source']);
                $imageUri = 'file://' . $relSrc;
                $assetInfo = ProjectAssetService::getInstance()->findByOrigin($imageUri);
                if (! $assetInfo)
                {
                    $assetInfo = ProjectAssetService::getInstance()->put($imageUri, $curImage, FALSE);
                }
                $images[] = $assetInfo->getIdentifier();
            }
        }
        return $images;
    }

    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>