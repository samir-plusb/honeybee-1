<?php

/**
 * The NitfNewswireDataRecord class is a concrete implementation of the NewswireDataRecord base class.
 * It provides processing data in the nitf format.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Tom Anheyer <tom.anheyer@berlinonline.de>
 * @package         Import
 * @subpackage      Newsire
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

    /**
     * Holds an array with known keys and xpath expressions as values.
     * This $expressionMap is used to evaluate and collect data from a given DOMDocument,
     * that has been initialized with imperia propetary xml.
     *
     * @var     array
     */
    protected static $expressionMap = array(
        self::PROP_IDENT        => '//doc-id/@id-string',
        self::PROP_TITLE        => '//head/title',
        self::PROP_SUBTITLE     => '//hedline/hl2',
        self::PROP_ABSTRACT     => '//abstract',
        self::PROP_CONTENT      => '//body.content/p',
        self::PROP_CATEGORY     => '//fixture/@fix-id',
        self::PROP_TIMESTAMP    => '//date.issue/@norm',
        self::PROP_DATE_RELEASE => '//date.release/@norm',
        self::PROP_DATE_EXPIRE  => '//date.expire/@norm',
        self::PROP_COPYRIGHT    => '//doc.copyright/@holder',
        self::PROP_KEYWORDS     => '//keyword/@key'
    );

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


    // ---------------------------------- <HYDRATE SETTERS> --------------------------------------

    /**
     * Set our subtitle during hydrate.
     *
     * @param       string $subtitle
     */
    protected function setSubtitle($subtitle)
    {
        $this->subtitle = $subtitle;
    }

    /**
     * Set our abstract during hydrate.
     *
     * @param       string $abstract
     */
    protected function setAbstract($abstract)
    {
        $this->abstract = $abstract;
    }

    /**
     * Set our release-date during hydrate.
     *
     * @param       string $release
     */
    protected function setRelease($release)
    {
        $this->release = $release;
    }

    /**
     * Set our expire-date during hydrate.
     *
     * @param       string $expire
     */
    protected function setExpire($expire)
    {
        $this->expire = $expire;
    }

    /**
     * Set our copyright during hydrate.
     *
     * @param       string $copyright
     */
    protected function setCopyright($copyright)
    {
        $this->copyright = $copyright;
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

    /**
     * Set our table(html string) during hydrate.
     *
     * @param       string $table
     */
    protected function setTable($table)
    {
        $this->table = $table;
    }


    /**
     * Set our timestamp from NITF issue date during hydrate.
     *
     * @param       string $table
     */
    protected function setTimestamp($param)
    {
        parent::setTimestamp(new DateTime($param));
    }

    // ---------------------------------- </HYDRATE SETTERS> -------------------------------------


    // ---------------------------------- <XmlBasedDataRecord IMPL> ------------------------------

    /**
     * Return an array holding fieldnames and corresponding xpath queries
     * that will be evaluated and mapped to the correlating field.
     *
     * @return      array
     *
     * @see         XmlBasedDataRecord::getFieldMap()
     */
    protected function getFieldMap()
    {
        return self::$expressionMap;
    }

    /**
     * Normalize the given xpath results.
     *
     * @param       array $data Contains result from processing our field map.
     *
     * @return      array
     *
     * @see         XmlBasedDataRecord::normalizeData()
     */
    protected function normalizeData(array $data)
    {
        $normalized = array(
            self::PROP_MEDIA => $this->importMedia(),
            self::PROP_TABLE => $this->importTable(),
            self::PROP_GEO   => array()
        );

        $normalized[self::PROP_CONTENT] = $this->joinNodeList($data[self::PROP_CONTENT], "\n\n");
        unset($data[self::PROP_CONTENT]);
        $normalized[self::PROP_KEYWORDS] = $this->nodeListToArray($data[self::PROP_KEYWORDS]);
        unset($data[self::PROP_KEYWORDS]);
        $normalized[self::PROP_IDENT] = $data[self::PROP_IDENT]->item(0)->nodeValue;
        unset($data[self::PROP_IDENT]);

        foreach ($data as $key => $nodeList)
        {
            if (!$nodeList)
            {
                continue;
            }

            $value = trim($nodeList->item(0)->nodeValue);

            if (preg_match('/^\d{8}T\d{6}[+-]\d{4}$/', $value))
            {
                $value = $value;
            }
            else if (preg_match('/^(\d{8}T\d{6})Z$/', $value, $m))
            {
                $value = $m[1].'+0000';
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }

    // ---------------------------------- </XmlBasedDataRecord IMPL> -----------------------------


    // ---------------------------------- <ImportBaseDataRecord OVERRIDES> -----------------------

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

    // ---------------------------------- </ImportBaseDataRecord OVERRIDES> ----------------------


    // ---------------------------------- <WORKING METHODS> --------------------------------------

    /**
     * import nitf tables
     *
     * @param       DOMDocument $domDoc
     *
     * @return      array of xml tagged strings
     */
    protected function importTable()
    {
        $domDoc = $this->getDocument();
        $data = array();
        $xpath = new DOMXPath($domDoc);

        foreach ($xpath->query('//table') as $table)
        {
            $data[] = $this->nodeToString($table);
        }

        return $data;
    }

    /**
     * import image media objects
     *
     * @param       DOMNode $item current nitf document
     * @param       array $feed_values feed entry values
     *
     * @return      array with reference information
     */
    protected function importMedia()
    {
        $domDoc = $this->getDocument();

        $media = array();
        $xpath = new DOMXPath($domDoc);

        foreach ($xpath->query("//media[@media-type='image']") as $mediaNode)
        {
            $pixels = -1;
            $image = array();

            foreach ($xpath->query('//media-reference', $mediaNode) as $mediaReference)
            {
                $attribute = $mediaReference->attributes;
                $width = intval($attribute->getNamedItem("width")->nodeValue);
                $height = intval($attribute->getNamedItem("height")->nodeValue);

                if ($pixels < ($width * $height))
                {
                    $image['source'] = htmlspecialchars($attribute->getNamedItem("source")->nodeValue);
                    $image['name'] = htmlspecialchars($attribute->getNamedItem("name")->nodeValue);
                    $image['alternate'] = htmlspecialchars($attribute->getNamedItem("alternate-text")->nodeValue);
                    $pixels = $width * $height;
                }
            }

            if (! empty ($image))
            {
                $captionNodeList = $xpath->query('media-caption', $mediaNode);
                $image['caption'] = $this->joinNodeList($captionNodeList, "\n");
                $media[] = $image;
            }
        }

        $images = array();

        foreach ($media as $image)
        {
            $relSrc = realpath(
                dirname($this->getOrigin()) . DIRECTORY_SEPARATOR . $image['source']
            );

            if ($relSrc)
            {
                unset($image['source']);
                $imageUri = 'file://'.$relSrc;
                $assetInfo = ProjectAssetService::getInstance()->put($imageUri, $image, FALSE);

                $images[] = $assetInfo->getId();
            }
        }

        return $images;
    }

    // ---------------------------------- </WORKING METHODS> -------------------------------------
}

?>