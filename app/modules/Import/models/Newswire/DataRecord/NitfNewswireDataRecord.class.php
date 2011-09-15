<?php

/**
 * NITF processing
 *
 * @version         $ID:$
 * @author          Tom Anheyer
 * @package         Import
 * @subpackage      Newswire
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
     * Holds the name of our issue property.
     */
    const PROP_KEYWORDS = 'keywords';

    /**
     * Holds the name of our issue property.
     */
    const PROP_DATE_ISSUE = 'issue';

    /**
     * Holds the name of our issue property.
     */
    const PROP_DATE_RELEASE = 'release';

    /**
     * Holds the name of our expire property.
     */
    const PROP_DATE_EXPIRE = 'expire';

    /**
     * Holds the base url to use when generating absolute links.
     *
     * @todo Move to config cause of evn awareness?
     */
    const LINK_BASE_URL = 'http://www.berlin.de';

    // ---------------------------------- <CONSTANTS> --------------------------------------------


    // ---------------------------------- <MEMBERS> ----------------------------------------------

    protected $subtitle;

    protected $abstract;

    protected $issue;

    protected $release;

    protected $expire;

    protected $copyright;

    protected $keywords;

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
        self::PROP_MEDIA        => '/imperia/body/article//image',
        self::PROP_CATEGORY     => '//fixture/@fix-id',
        self::PROP_DATE_ISSUE   => '//date.issue/@norm',
        self::PROP_DATE_RELEASE => '//date.release/@norm',
        self::PROP_DATE_EXPIRE  => '//date.expire/@norm',
        self::PROP_COPYRIGHT    => '//doc.copyright/@holder',
        self::PROP_KEYWORDS     => '//keyword/@key'
    );

    protected static $expressionProcessors = array(
        self::PROP_IDENT        => 'extractFirst',
        self::PROP_TITLE        => 'extractFirst',
        self::PROP_CONTENT      => 'extractCollection',
        self::PROP_SUBTITLE     => 'extractFirst',
        self::PROP_COPYRIGHT    => 'extractFirst',
        self::PROP_DATE_ISSUE   => 'extractFirst',
        self::PROP_DATE_RELEASE => 'extractFirst',
        self::PROP_DATE_EXPIRE  => 'extractFirst'
    );

    // ---------------------------------- </MEMBERS> ---------------------------------------------

    // ---------------------------------- <IDataRecord IMPL> -------------------------------------

    /**
     * Returns a hopefully unique identifier.
     *
     * @return      string
     *
     * @see         IDataRecord::getIdentifier()
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    // ---------------------------------- </IDataRecord IMPL> ------------------------------------


    // ---------------------------------- <PUBLIC METHODS> ---------------------------------------

    public function getSubtitle()
    {
        return $this->subtitle;
    }

    public function getAbstract()
    {
        return $this->abstract;
    }

    public function getDateIssue()
    {
        return $this->issue;
    }

    public function getDateRelease()
    {
        return $this->release;
    }

    public function getDateExpire()
    {
        return $this->expire;
    }

    public function getCopyright()
    {
        return $this->copyright;
    }

    public function getKeywords()
    {
        return $this->keywords;
    }

    // ---------------------------------- </PUBLIC METHODS> --------------------------------------


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
            self::PROP_MEDIA => $this->importMedia()
        );
        //$normalized['table'] = $this->importTable();

        $normalized[self::PROP_CONTENT] = $this->joinNodeList($data[self::PROP_CONTENT], "\n\n");
        $normalized[self::PROP_KEYWORDS] = $this->nodeListToArray($data[self::PROP_KEYWORDS]);
        list($normalized[self::PROP_IDENT]) = explode(':', $data[self::PROP_IDENT]->item(0)->nodeValue);

        unset($data[self::PROP_IDENT]);
        unset($data[self::PROP_CONTENT]);
        unset($data[self::PROP_KEYWORDS]);

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

    protected function getExposedProperties()
    {
        return array_merge(
            parent::getExposedProperties(),
            array(
                self::PROP_SUBTITLE,
                self::PROP_ABSTRACT,
                self::PROP_KEYWORDS,
                self::PROP_COPYRIGHT,
                self::PROP_DATE_ISSUE,
                self::PROP_DATE_RELEASE,
                self::PROP_DATE_EXPIRE
            )
        );
    }

    // ---------------------------------- </XmlBasedDataRecord IMPL> -----------------------------

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
}

?>