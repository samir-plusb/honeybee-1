<?php

/**
 * The ImperiaDataRecord class is a concrete implementation of the XmlBasedDataRecord base class.
 * It serves as the base class to all imperia related IDataRecord implementations.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Imperia
 */
abstract class ImperiaDataRecord extends XmlBasedDataRecord
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    /**
     * Holds the name of our subtitle property.
     */
    const PROP_SUBTITLE = 'subtitle';

    /**
     * Holds the name of our kicker property.
     */
    const PROP_KICKER = 'kicker';

    /**
     * Holds the name of our link.
     */
    const PROP_LINK = 'link';

    /**
     * Holds the name of imerpia's directory node.
     */
    const PROP_DIRECTORY = 'directory';

    /**
     * Holds the name of imerpia's filename node.
     */
    const PROP_FILENAME = 'filename';

    /**
     * Imperia publish date
     */
    const PROP_PUBLISH = 'publishDate';

    /**
     * Imperia expiry date
     */
    const PROP_EXPIRY = 'expiryDate';

    /**
     * keywords meta field
     */
    const PROP_KEYWORDS = 'keywords';

    /**
     * Holds the base url to use when generating absolute links.
     *
     * @todo Move to config cause of evn awareness?
     */
    const LINK_BASE_URL = 'http://www.berlin.de';

    // ---------------------------------- <CONSTANTS> --------------------------------------------


    // ---------------------------------- <MEMBERS> ----------------------------------------------

    /**
     * Holds our subtitle.
     *
     * @var         string
     */
    protected $subtitle;

    /**
     * Holds our kicker.
     *
     * @var         string
     */
    protected $kicker;

    /**
     * Holds our directory.
     * This value is used together with our $filename property
     * to build our $link properties value.
     *
     * @var         string
     */
    protected $directory;

    /**
     * Holds our filename.
     *
     * @var         string
     */
    protected $filename;

    /**
     * Holds the link pointing to our web representation.
     *
     * @var         string
     */
    protected $link;

    /**
     * Imperia publish date
     * @var DateTime
     */
    protected $publishDate;

    /**
     * Imperia expiry date
     * @var DateTime
     */
    protected $expiryDate;

    /**
     * Keywords
     * @var string
     */
    protected $keywords;

    /**
     * Holds an array with known keys and xpath expressions as values.
     * This $expressionMap is used to evaluate and collect data from a given DOMDocument,
     * that has been initialized with imperia propetary xml.
     *
     * @var     array
     */
    protected static $expressionMap = array(
        self::PROP_TITLE     => '/imperia/body/article/title',
        self::PROP_SUBTITLE  => '/imperia/body/article/subtitle',
        self::PROP_KICKER    => '/imperia/body/article/kicker',
        self::PROP_CONTENT   => '/imperia/body/article//paragraph/text',
        self::PROP_CATEGORY  => '/imperia/head/categories/category',
        self::PROP_DIRECTORY => '/imperia/head/directory',
        self::PROP_FILENAME  => '/imperia/head/filename',
        self::PROP_MEDIA     => '/imperia/body/article//image',
        self::PROP_SOURCE    => '/imperia/head/categories/category',
        self::PROP_TIMESTAMP => '/imperia/head/modified',
        self::PROP_PUBLISH   => '/imperia/head/publish',
        self::PROP_EXPIRY    => '/imperia/head/expiry',
        self::PROP_KEYWORDS  => '/imperia/head/meta[@name="keywords"]/@content'
        );

    /**
     * An array used to map the results of evaluating our expression map
     * to a set of corresponding processors, that extract our final values.
     *
     * @var         array
     */
    protected static $expressionProcessors = array(
            self::PROP_TITLE     => 'extractFirst',
            self::PROP_CONTENT   => 'extractCollection',
            self::PROP_CATEGORY  => 'extractCategory',
            self::PROP_SUBTITLE  => 'extractFirst',
            self::PROP_KICKER    => 'extractFirst',
            self::PROP_DIRECTORY => 'extractFirst',
            self::PROP_FILENAME  => 'extractFirst',
            self::PROP_MEDIA     => 'extractMedia',
            self::PROP_SOURCE    => 'extractSource',
            self::PROP_TIMESTAMP => 'extractTimestamp',
            self::PROP_PUBLISH   => 'extractPublishDate',
            self::PROP_EXPIRY    => 'extractExpiryDate'
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
        return $this->getLink();
    }

    // ---------------------------------- </IDataRecord IMPL> ------------------------------------


    // ---------------------------------- <PUBLIC METHODS> ---------------------------------------

    /**
     * Return our subtitle.
     *
     * @return      string
     */
    public function getSubtitle()
    {
        return $this->subtitle;
    }

    /**
     * Return our kicker.
     *
     * @return      string
     */
    public function getKicker()
    {
        return $this->kicker;
    }

    /**
     * Return our link.
     *
     * @return      string
     */
    public function getLink()
    {
        return $this->link;
    }


    /**
     * get ISO8601 formatted publish date
     *
     * @return      string
     */
    public function getPublishDate()
    {
        return $this->publishDate instanceof DateTime
            ? $this->publishDate->format(DATE_ISO8601)
            : $this->getTimestamp();
    }

    /**
     * get ISO8601 formatted expiry date
     *
     * @return      string
     */
    public function getExpiryDate()
    {
        print_r($this->expiryDate);
        return $this->expiryDate instanceof DateTime
            ? $this->expiryDate->format(DATE_ISO8601)
            : NULL;
    }

    /**
     * get keywords.
     *
     * @return      string
     */
    public function getKeywords()
    {
        return $this->keywords;
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
     * Set our subtitle during hydrate.
     *
     * @param       string $subtitle
     */
    protected function setKicker($kicker)
    {
        $this->kicker = $kicker;
    }

    /**
     * Set our directory during hydrate.
     *
     * @param       string $directory
     */
    protected function setDirectory($directory)
    {
        $this->directory = $directory;
        $this->applyLink();
    }

    /**
     * Set our filename during hydrate.
     *
     * @param       string $filename
     */
    protected function setFilename($filename)
    {
        $this->filename = $filename;
        $this->applyLink();
    }


    /**
     * Set our publish date during hydrate.
     *
     * @param       DateTime $param
     */
    protected function setPublishDate(DateTime $param = NULL)
    {
        $this->publishDate = $param;
    }

    /**
     * Set our expiry date during hydrate.
     *
     * @param       DateTime $param
     */
    protected function setExpiryDate(DateTime $param = NULL)
    {
        $this->expiryDate = $param;
    }

    /**
     * Set our keywords during hydrate.
     *
     * @param       string $param
     */
    protected function setKeywords($param)
    {
        $this->keywords = $param;
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
    protected function normalizeData(array $xPathResults)
    {
        $data = array();

        foreach (self::$expressionProcessors as $propName => $processor)
        {
            if (is_callable(array($this, $processor)))
            {
                $data[$propName] = $this->$processor($xPathResults, $propName);
            }
        }

        $data[self::PROP_GEO] = array();

        return $data;
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
                self::PROP_SUBTITLE,
                self::PROP_KICKER,
                self::PROP_LINK,
                self::PROP_PUBLISH,
                self::PROP_EXPIRY,
                self::PROP_KEYWORDS
            )
        );

    }

    // ---------------------------------- </XmlBasedDataRecord IMPL> -----------------------------


    // ---------------------------------- <WORKING METHODS> --------------------------------------

    /**
     * Generates and assigns our link property.
     */
    protected function applyLink()
    {
        if ($this->filename && $this->directory)
        {
            $this->link = sprintf(
                "%s%s/%s",
                self::LINK_BASE_URL,
                $this->directory,
                $this->filename
            );
        }
    }

    /**
     * Extracts the value of the first node in a node list.
     * The node list is pulled from the data that resulted
     * from processing our $expressionMap.
     *
     * @param       array $xPathResults
     * @param       string $key
     *
     * @return      mixed
     */
    protected function extractFirst(array $xPathResults, $key)
    {
        if (!$xPathResults[$key] || 0 === $xPathResults[$key]->length)
        {
            return NULL;
        }

        return trim($xPathResults[$key]->item(0)->nodeValue);
    }

    /**
     * Converts a node list to a collection mixed values that have been pulled from the nodes.
     * The node list is pulled from the data that resulted
     * from processing our $expressionMap.
     *
     * @param       array $xPathResults
     * @param       string $key
     *
     * @return      mixed
     */
    protected function extractCollection(array $xPathResults, $key)
    {
        if (!$xPathResults[$key] || !$xPathResults[$key])
        {
            return array();
        }

        return $this->joinNodeList($xPathResults[$key], "\n\n");
    }

    /**
     * Extracts the imperia category nodes and returns
     * their joined string representation.
     *
     * @param       array $xPathResults
     * @param       string $key
     *
     * @return      mixed
     */
    protected function extractCategory(array $xPathResults, $key)
    {
        $categoryCrumbs = $this->nodeListToArray($xPathResults[$key]);

        return sprintf('// %s', join(' // ', array_reverse($categoryCrumbs)));
    }

    /**
     * Extracts the organistion from category info
     *
     * @param       array $xPathResults
     * @param       string $key
     *
     * @return      mixed
     */
    protected function extractSource(array $xPathResults, $key)
    {
        $categoryCrumbs = $this->nodeListToArray($xPathResults[$key]);
        $cat = array_reverse($categoryCrumbs);
        if ('Land' != $cat[0])
        {
            return 'Imperia-Unknown';
        }
        if ('Senatsverwaltungen' == $cat[1])
        {
            return $cat[2];
        }
        else
        {
            return $cat[1];
        }
    }

    /**
     * Convert imperia export timestamp into a DateTime instance.
     *
     * @param       string $timestamp parseable by strtotime
     *
     * @return      DateTime
     */
    protected function extractTimestamp(array $xPathResults)
    {
        return new DateTime($this->extractFirst($xPathResults, 'timestamp'));
    }


    /**
     * Convert imperia export timestamp into a DateTime instance.
     *
     * @param       string $timestamp parseable by strtotime
     *
     * @return      DateTime
     */
    protected function extractPublishDate(array $xPathResults)
    {
        $value = $this->extractFirst($xPathResults, 'publishDate');
        return empty($value) ? NULL : new DateTime($value);
    }

    /**
     * Convert imperia export timestamp into a DateTime instance.
     *
     * @param       string $timestamp parseable by strtotime
     *
     * @return      DateTime
     */
    protected function extractExpiryDate(array $xPathResults)
    {
        $value = $this->extractFirst($xPathResults, 'expiryDate');
        return empty($value) ? NULL : new DateTime($value);
    }

    /**
     * Extracts the media found inside our parsed data
     * and creates asset items thereby returning an array
     * containing the id's of all stored assets.
     *
     * @param       array $xPathResults
     * @param       string $key
     *
     * @return      array
     */
    protected function extractMedia(array $xPathResults, $key)
    {
        $assets = array();

        if (!isset($xPathResults[$key]) || !$xPathResults[$key])
        {
            return $assets;
        }

        foreach ($xPathResults[$key] as $imageNode)
        {
            $assets[] = $this->createAsset($imageNode);
        }

        return $assets;
    }

    /**
     * Create an AssetInfo instance from the given DOMNode
     * and return it's id.
     *
     * @param       DOMNode $imageNode
     *
     * @return      integer
     */
    protected function createAsset(DOMNode $imageNode)
    {
        $metaDataNodes = array('caption');
        $metaData = array();
        $src = NULL;

        foreach ($imageNode->childNodes as $childNode)
        {
            if ('src' === $childNode->nodeName)
            {
                $src = self::LINK_BASE_URL . trim($childNode->nodeValue);
            }
            elseif (in_array($childNode->nodeName, $metaDataNodes))
            {
                $metaData[$childNode->nodeName] = trim($childNode->nodeValue);
            }
        }

        if ($src)
        {
            $assetInfo = ProjectAssetService::getInstance()->put($src, $metaData);

            return $assetInfo->getId();
        }

        return NULL;
    }

    // ---------------------------------- <WORKING METHODS> --------------------------------------
}

?>