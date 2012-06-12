<?php

/**
 * The BaseDataRecord class is an abstract implementation of the IDataRecord interface.
 * It provides an base implementation of most IDataRecord methods and provides template methods
 * for inheriting classes to hook into normalizing the incoming data on record creation.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      DataRecord
 */
abstract class BaseDataRecord implements IDataRecord, IComparable
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    /**
     * Holds the name of our identifier property.
     */
    const PROP_IDENT = 'identifier';

    /**
     * DATE_ISO8601 formated timestamp (last change of record, issue date, import date)
     */
    const PROP_TIMESTAMP = 'timestamp';

    /**
     * Holds the name of our origin property.
     */
    const PROP_ORIGIN = 'origin';

    /**
     * Holds the name of our source property.
     */
    const PROP_SOURCE = 'source';

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
     * Create new BaseDataRecord instance,
     * thereby parsing the given data to setup state.
     *
     * @param       mixed $data
     * @param       string
     *
     * @uses        BaseDataRecord::parse()
     * @uses        BaseDataRecord::hydrate()
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
            self::PROP_TIMESTAMP
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
            self::PROP_TIMESTAMP
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
            if (array_key_exists($propName, $data))
            {
                $setter = 'set'.ucfirst($propName);

                if (is_callable(array($this, $setter)))
                {
                    $this->$setter($value);
                }
                else
                {
                    $this->$propName = $value;
                }
            }
        }
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