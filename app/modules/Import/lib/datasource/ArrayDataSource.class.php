<?php

/**
 * The ArrayDataSource class is a concrete implementation of the BaseDataSource base class.
 * It basically just wraps a standard php array and exposes it through the IDataSource interface,
 * thereby allowing simple integration of your various runtime data.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Import
 * @subpackage      DataSource
 */
class ArrayDataSource extends BaseDataSource
{
    // ---------------------------------- <MEMBERS> ----------------------------------------------

    /**
     * Holds whatever data was passed into here by config param.
     * Remember that your configured IDataRecord implementations have to able
     * to handle everything inside here.
     *
     * @var         array
     */
    protected $data;

    /**
     * Holds our current position while iterating over our mails.
     *
     * @var         int
     */
    protected $cursorPos;

    /**
     * Holds the max number of iterations possible,
     * depending on the number of available mails.
     *
     * @var         int
     */
    protected $maxCount;

    // ---------------------------------- </MEMBERS> ---------------------------------------------


    // ---------------------------------- <BaseDataSource OVERRIDES> -----------------------

    /**
     * Initialize our datasource, hence connect our mailbox
     * and init our iteration variables.
     */
    protected function init()
    {
        $this->data = $this->initData();
        $this->maxCount = count($this->data);
        $this->cursorPos = -1;
    }

    protected function initData()
    {
        return $this->config->getSetting(
            ArrayDataSourceConfig::CFG_DATA,
            array()
        );
    }

    /**
     * Forward our current position inside the mail list
     * that we are currently iterating.
     *
     * @return      boolean
     */
    protected function forwardCursor()
    {
        $this->cursorPos++;

        return $this->cursorPos < $this->maxCount;
    }

    /**
     * Return the data at the offset which our cursor is currently pointing to.
     *
     * @return      array
     */
    protected function fetchData()
    {
        if (! isset($this->data[$this->cursorPos]))
        {
            throw new DataSourceException(
                "Invalid cursor position detected for ArrayDataSource. There is no data at index: " . $this->cursorPos
            );
        }

        return $this->data[$this->cursorPos];
    }

    // ---------------------------------- </BaseDataSource OVERRIDES> ----------------------
}

?>