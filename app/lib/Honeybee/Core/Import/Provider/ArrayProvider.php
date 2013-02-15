<?php

namespace Honeybee\Core\Import\Provider;

/**
 * The ArrayProvider class is a concrete implementation of the BaseProvider base class.
 * It basically just wraps a standard php array and exposes it through the IProvider interface,
 * thereby allowing simple integration of your various runtime data.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 */
class ArrayProvider extends BaseProvider
{
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

    /**
     * Initialize the provider with extrinsic parameters.
     *
     * @param       array $parameters
     */
    public function initialize(array $parameters = array())
    {
        parent::initialize($parameters);

        $data = array();

        if (isset($parameters['data']) && is_array($parameters['data']))
        {
            $data = $parameters['data'];
        }

        $this->data = $data;
        $this->maxCount = count($this->data);
        $this->cursorPos = -1;
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
            throw new Exception(
                "Invalid cursor position detected for ArrayProvider. There is no data at index: " . $this->cursorPos
            );
        }

        return $this->data[$this->cursorPos];
    }

    protected function getCurrentOrigin()
    {
        return 'index pos: ' . $this->cursorPos;
    }
}
