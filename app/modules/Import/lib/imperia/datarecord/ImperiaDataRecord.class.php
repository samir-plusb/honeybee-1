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
class ImperiaDataRecord extends ImportBaseDataRecord
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
    const LINK_BASE_URL_SETTING = 'import.imperia_datarecord.base_href';

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


    // ---------------------------------- <ImportBaseDataRecord IMPL> ----------------------------

    /**
     * Parse the given xml data and return a normalized array.
     *
     * @param       mixed $data
     *
     * @return      array
     *
     * @see         ImportBaseDataRecord::parseData()
     */
    protected function parseData($data)
    {
        $parser = new ImperiaXmlParser();
        $parsedData = $parser->parseXml($data);

        $recordData = array();
        $recordData[self::PROP_TITLE] = $parsedData['title'];
        $recordData[self::PROP_SUBTITLE] = $parsedData['subtitle'];
        $recordData[self::PROP_DIRECTORY] = $parsedData['directory'];
        $recordData[self::PROP_FILENAME] = $parsedData['filename'];
        $recordData[self::PROP_KICKER] = $parsedData['kicker'];
        $recordData[self::PROP_TIMESTAMP] = $parsedData['modified'];
        $recordData[self::PROP_EXPIRY] = $parsedData['expiry'];
        $recordData[self::PROP_PUBLISH] = $parsedData['publish'];
        $recordData[self::PROP_SOURCE] = $this->generateSource($parsedData['categories']);
        $recordData[self::PROP_CONTENT] = implode("\n\n", $parsedData['paragraphs']);
        $recordData[self::PROP_CATEGORY] = sprintf('// %s', join(' // ', array_reverse($parsedData['categories'])));
        $recordData[self::PROP_GEO] = array();
        $recordData[self::PROP_MEDIA] = array();

        foreach ($parsedData['images'] as $imageInfo)
        {
            try
            {
                $recordData[self::PROP_MEDIA][] = $this->createAsset($imageInfo);
            }
            catch(Exception $e)
            {
                if (0 !== strpos(AgaviConfig::get('core.environment'), 'testing.*'))
                {
                    // @todo add environment based logginf configs
                    // so we can transparently log to agavi without spamming the app log.
                    $this->logError(
                        sprintf(
                            "Failed to download asset from url: %s with error: %s",
                            print_r($imageInfo, TRUE),
                            PHP_EOL . $e->getMessage()
                        )
                    );
                }
            }
        }
        return $recordData;
    }

    // ---------------------------------- </ImportBaseDataRecord IMPL> ---------------------------


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
                self::PROP_KICKER,
                self::PROP_LINK,
                self::PROP_PUBLISH,
                self::PROP_EXPIRY,
                self::PROP_KEYWORDS
            )
        );

    }

    // ---------------------------------- <ImportBaseDataRecord OVERRIDES> -----------------------


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
                AgaviConfig::get(self::LINK_BASE_URL_SETTING),
                $this->directory,
                $this->filename
            );
        }
    }

    /**
     * Generate the record's source info from the given categories.
     *
     * @param       array $xPathResults
     * @param       string $key
     *
     * @return      mixed
     */
    protected function generateSource(array $categories)
    {
        $catReverse = array_reverse($categories);

        if ('Land' != $catReverse[0])
        {
            return 'Imperia-Unknown';
        }

        if ('Senatsverwaltungen' == $catReverse[1])
        {
            return $catReverse[2];
        }

        return $catReverse[1];
    }

    /**
     * Create an AssetInfo instance from the given image data and returns it's id.
     *
     * @param       array $imageInfo
     *
     * @return      integer
     */
    protected function createAsset(array $imageInfo)
    {
        $src = AgaviConfig::get(self::LINK_BASE_URL_SETTING) . $imageInfo['src'];
        unset($imageInfo['src']);

        return ProjectAssetService::getInstance()->put($src, $imageInfo)->getId();
    }

    // ---------------------------------- <WORKING METHODS> --------------------------------------
}

?>