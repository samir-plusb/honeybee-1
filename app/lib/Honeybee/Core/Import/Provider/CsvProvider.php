<?php

namespace Honeybee\Core\Import\Provider;

/**
 * The CsvProvider class is a concrete implementation of the BaseProvider base class.
 * It provides access to CSV formatted data.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 */
class CsvProvider extends BaseProvider
{
    /**
     * @var array of fieldnames from the CSV header row
     */
    protected $field_map;

    /**
     * @var resource file pointer that handles access to the CSV source file
     */
    protected $csv_handle;

    /**
     * @var array currently loaded row from CSV source file
     */
    protected $current_row;

    /**
     * @var int number of columns in a complete CSV import row
     */
    protected $correct_column_count;

    /**
     * @var string separator used in CSV file
     */
    protected $separator;

    /**
     * Initialize the provider with extrinsic parameters.
     *
     * @param array $parameters
     */
    public function initialize(array $parameters = array())
    {
        parent::initialize($parameters);

        $csv_source_file = realpath($this->getConfig()->get('filepath'));
        $this->csv_handle = fopen($csv_source_file, 'r');

        if (false === $this->csv_handle)
        {
            throw new \Exception("Unable to initialize handle for CSV source file with path " . $this->getConfig()->get('filepath') . ".");
        }

        $this->separator = $this->getConfig()->get('separator', ';');

        $this->field_map = $this->buildFieldMap($this->csv_handle);
    }

    /**
     * Forward current position inside the list we currently iterate (to the
     * next row from the CSV source file).
     *
     * @return boolean true if cursor was forwarded. False otherwise.
     */
    protected function forwardCursor()
    {
        $this->current_row = fgetcsv($this->csv_handle, 0, $this->separator);

        if ($this->getConfig()->get('ignore_incomplete_rows', false))
        {
            while ($this->current_row !== false && !$this->isRowComplete($this->current_row))
            {
                $this->current_row = fgetcsv($this->csv_handle, 0, $this->separator);
            }
        }

        return !!$this->current_row;
    }

    /**
     * Return the data at the offset which our cursor is currently pointing to.
     *
     * @return      array
     */
    protected function fetchData()
    {
        $data = array();

        foreach ($this->field_map as $field_name => $pos)
        {
            if (isset($this->current_row[$pos]))
            {
                $data[$field_name] = $this->current_row[$pos];
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
     * Return an associative array that maps fieldnames to csv column indizes.
     *
     * The mapping is either taken from the header row of the CSV file or
     * read from the config via the 'fieldmap' setting.
     *
     * @return array assocative array of fieldname => CSV column index
     */
    protected function buildFieldMap($csv_handle)
    {
        $field_map = array();

        if ($this->getConfig()->has('fieldmap'))
        {
            fgetcsv($csv_handle, 0, ';');
            $field_map = $this->getConfig()->get('fieldmap');
        }
        else
        {
            $field_map = array_flip(fgetcsv($csv_handle, 0, $this->separator));
        }

        $this->correct_column_count = count($field_map);

        return $field_map;
    }

    /**
     * @param array $row currently loaded row from CSV source file
     *
     * @return boolean true if row count is the same as the fieldmap column row count
     */
    protected function isRowComplete($row)
    {
        return (count($row) === $this->correct_column_count);
    }
}
