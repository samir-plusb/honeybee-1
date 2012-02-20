<?php

/**
 * The ImportBaseDataRecord class is an abstract implementation of the IDataRecord interface.
 * It provides an base implementation of most IDataRecord methods and provides template methods
 * for inheriting classes to hook into normalizing the incoming data on record creation.
 *
 * @version         $Id:$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      Base
 */
abstract class ImportBaseDataRecord implements IDataRecord, IComparable
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    /**
     * Holds a prefix used to build the names of getter methods,
     * by combinig the prefix with property names.
     */
    const METHOD_PREFIX_GETTER = 'get';

    /**
     * Same as the self::METHOD_PREFIX_GETTER, just for setters.
     */
    const METHOD_PREFIX_SETTER = 'set';

    /**
     * Same as the self::METHOD_PREFIX_GETTER, just for validation.
     */
    const METHOD_PREFIX_VALIDATE = 'validate';

    // ---------------------------------- </CONSTANTS> -------------------------------------------


    // ---------------------------------- <MEMBERS> ----------------------------------------------

    /**
     * Holds our config object, used to conigure things such as our source and origin.
     *
     * @var         DataRecordConfig
     */
    protected $config;

    /**
     * Holds our unique identifier.
     *
     * @var         string
     */
    private $identifier;

    /**
     * This records timestampcan be record last change time, message issue date, mail date, ...
     *
     * @var         DateTime
     */
    protected $timestamp;

    /**
     * Holds this IDataRecord's source.
     * Will usually be a name or term related to the datasource
     * that created this record instance.
     *
     * @var         string
     */
    protected $source;

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

    // ---------------------------------- </MEMBERS> ---------------------------------------------


    // ---------------------------------- <ABSTRACT METHODS> -------------------------------------

    /**
     * Normalize the incoming data.
     * This probally the most important method for subclasses to implement,
     * as this where you take your arbitary data(array, file, xml, whatever ...)
     * and bring it into an usable array structure with keys that map to our provided fieldnames.
     *
     * @param       mixed $data
     *
     * @return      array
     */
    abstract protected function parseData($data);

    // ---------------------------------- </ABSTRACT METHODS> ------------------------------------


    // ---------------------------------- <CONSTRCUTOR> ------------------------------------------

    /**
     * Create new ImportBaseDataRecord instance,
     * thereby parsing the given data to setup state.
     *
     * @param       mixed $data
     * @param       string
     *
     * @uses        ImportBaseDataRecord::parse()
     * @uses        ImportBaseDataRecord::hydrate()
     */
    public function __construct($data, DataRecordConfig $config)
    {
        $this->config = $config;

        $this->source = $this->config->getSetting(DataRecordConfig::CFG_SOURCE);

        $this->hydrate(
            $this->parseData($data)
        );
    }

    // ---------------------------------- </CONSTRCUTOR> -----------------------------------------


    // ---------------------------------- <IDataRecord IMPL> -------------------------------------

    /**
     * Return an unique identifier that represents this record.
     *
     * @return      string
     *
     * @see         IDataRecord::getIdentifier()
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Return our source.
     *
     * @return      string
     *
     * @see         IDataRecord::getSource()
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Return our timestamp
     *
     * @return      string DATE_ISO8601 formatted timestamp
     *
     * @see         IDataRecord::getTimestamp()
     */
    public function getTimestamp()
    {
        return $this->timestamp instanceof DateTime
            ? $this->timestamp->format(DATE_ISO8601)
            : date(DATE_ISO8601);
    }

    /**
     * Return our origin.
     *
     * @return      string
     *
     * @see         IDataRecord::getOrigin()
     */
    public function getOrigin()
    {
        return $this->config->getSetting(DataRecordConfig::CFG_ORIGIN);
    }

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
     * Return an array representation of the given record.
     *
     * @return      array
     *
     * @see         IDataRecord::toArray()
     */
    public function toArray()
    {
        $data = array();

        foreach ($this->getExposedProperties() as $propName)
        {
            $getterMethod = self::METHOD_PREFIX_GETTER . ucfirst($propName);

            if (is_callable(array($this, $getterMethod)))
            {
                $data[$propName] = $this->$getterMethod();
            }
        }

        return $data;
    }

    /**
     * Validates that the given record is in a consistent state
     * and is ready to be thrown into the domain.
     * Subclasses may implement a validate{PROP_NAME} method,
     * if they want to hook into the validaton process for certain properties.
     *
     * @return      IRecordValidationResult
     *
     * @see         IDataRecord::validate()
     */
    public function validate()
    {
        $data = $this->toArray();

        $validationResult = new RecordValidationResult();

        foreach ($this->getRequiredProperties() as $propName)
        {
            if (!isset($data[$propName]))
            {
                $errName = 'missing_' . $propName;
                $validationResult->addError(
                    $errName,
                    sprintf(
                        "The property %s is mandatory but not set.",
                        $propName
                    )
                );
            }

            $validationMethod = self::METHOD_PREFIX_VALIDATE . ucfirst($propName);

            if (is_callable(array($this, $validationMethod)))
            {
                $this->$validationMethod($validationResult);
            }
        }

        return $validationResult;
    }

    // ---------------------------------- </IDataRecord IMPL> ------------------------------------


    // ---------------------------------- <ICompareable IMPL> ------------------------------------

    /**
     * Compares ourself to a given $other IDataRecord.
     *
     * @param       IDataRecord $other
     *
     * @return      int Returns 0 if equal and -1 not.
     *
     * @throws      DataRecordException If the given $other is no instance of IDataRecord.
     */
    public function compareTo($other)
    {
        if (!($other instanceof IDataRecord))
        {
            throw new DataRecordException("Unable to compare non-datarecord type with data-record.");
        }

        $compareData = $other->toArray();

        foreach ($this->toArray() as $field => $value)
        {
            if ($compareData[$field] != $value)
            {
                return -1;
            }
        }

        return 0;
    }

    // ---------------------------------- </ICompareable IMPL> -----------------------------------


    // ---------------------------------- <WORKING METHODS> --------------------------------------

    /**
     * Return an array holding property names of properties,
     * which we want to expose through our IDataRecord::toArray() method.
     *
     * @return      array
     */
    protected function getExposedProperties()
    {
        return array(
            self::PROP_IDENT,
            self::PROP_SOURCE,
            self::PROP_TIMESTAMP,
            self::PROP_TITLE,
            self::PROP_CONTENT,
            self::PROP_CATEGORY,
            self::PROP_MEDIA,
            self::PROP_GEO
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
        return array(
            self::PROP_IDENT,
            self::PROP_SOURCE,
            self::PROP_TIMESTAMP,
            self::PROP_TITLE,
            self::PROP_CONTENT,
            self::PROP_MEDIA,
            self::PROP_GEO
        );
    }

    /**
     * Hydrate the given data into our object.
     *
     * @param       array $data
     */
    protected function hydrate(array $data)
    {
        foreach ($data as $propName => $value)
        {
            $setterMethod = self::METHOD_PREFIX_SETTER . ucfirst($propName);

            if (is_callable(array($this, $setterMethod)))
            {
                $this->$setterMethod($value);
            }
        }
    }

    /**
     * Set our identifier.
     *
     * @param       string
     *
     * @see         IDataRecord::getSource()
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function setIdentifier($identifier)
    {
        $this->identifier = sha1($identifier);
    }

    /**
     * Set our timestamp.
     *
     * @param       DateTime $timestamp value, can be NULL
     *
     * @see         IDataRecord::getTimestamp()
     */
    protected function setTimestamp(DateTime $timestamp = NULL)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * Set our identifier.
     *
     * @param       string
     *
     * @see         IDataRecord::getSource()
     */
    protected function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * Set our title.
     *
     * @param       string
     *
     * @see         IDataRecord::setTitle()
     */
    protected function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Set our content.
     *
     * @param       string
     *
     * @see         IDataRecord::setContent()
     */
    protected function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Set our category.
     *
     * @param       string
     *
     * @see         IDataRecord::setCategory()
     */
    protected function setCategory($category)
    {
        $this->category = $category;
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

    // ---------------------------------- </WORKING METHODS> -------------------------------------

    protected function logError($msg)
    {
        $logger = AgaviContext::getInstance()->getLoggerManager()->getLogger('error');
        $logger->log(
            new AgaviLoggerMessage($msg, AgaviLogger::ERROR)
        );
    }
}

?>