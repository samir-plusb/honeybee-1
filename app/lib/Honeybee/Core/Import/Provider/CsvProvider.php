<?php

namespace Honeybee\Core\Import\Provider;

/**
 * The CsvProvider class is a concrete implementation of the BaseProvider base class.
 * It provides access to csv formatted data.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 */
class CsvProvider extends BaseProvider
{
    /**
     * Holds an array of fieldnames pulled from the csv file's first line.
     *
     * @var         array
     */
    protected $fieldMap;

    /**
     * Holds a file pointer that handles access to our csv source.
     *
     * @var         resource
     */
    protected $csvHandle;

    /**
     * Holds the currently loaded csv row.
     *
     * @var         array
     */
    protected $currentRow;

    /**
     * Initialize the provider with extrinsic parameters.
     *
     * @param       array $parameters
     */
    public function initialize(array $parameters = array())
    {
        parent::initialize($parameters);

        $csvSource = realpath($this->getConfig()->get('filepath'));
        $this->csvHandle = fopen($csvSource, 'r');

        if (FALSE === $this->csvHandle)
        {
            throw new Exception("Unable to initialize handle for csv-source.");
        }

        $this->fieldMap = $this->buildFieldMap($this->csvHandle);
    }

    /**
     * Forward our current position inside the mail list
     * that we are currently iterating.
     *
     * @return      boolean
     */
    protected function forwardCursor()
    {
        $this->currentRow = fgetcsv($this->csvHandle, 0, ';');

        return !!$this->currentRow;
    }

    /**
     * Return the data at the offset which our cursor is currently pointing to.
     *
     * @return      array
     */
    protected function fetchData()
    {
        $data = array();

        foreach ($this->fieldMap as $fieldName => $pos)
        {
            if (isset($this->currentRow[$pos]))
            {
                $data[$fieldName] = $this->currentRow[$pos];
            }
        }

        return $data;
    }

    /**
     * Return the a string that identifies our data origin.
     *
     * @return      string
     */
    protected function getCurrentOrigin()
    {
        return realpath($this->getConfig()->get('filepath'));
    }

    /**
     * Return an assoc array that maps fieldnames t csv column indexes.
     *
     * @return      array
     */
    protected function buildFieldMap($csvHandle)
    {
        $fieldMap = array();

        if ($this->getConfig()->has('fieldmap'))
        {
            fgetcsv($csvHandle, 0, ';');
            $fieldMap = $this->getConfig()->get('fieldmap');
        }
        else
        {
            $fieldMap = array_flip(fgetcsv($csvHandle, 0, ';'));
        }

        return $fieldMap;
    }
}
