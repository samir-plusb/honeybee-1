<?php

namespace Honeybee\Core\Import\Filter;

/**
 * IFilter implementations are responseable for wrapping data manipulation during import such as 
 * converting arrays from one structure to another, preparing binaries etc..
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 */
interface IFilter
{
    /**
     * Return the name of our filter.
     *
     * @return      string
     */
    public function getName();

    /**
     * Initialize the filter with extrinsic parameters.
     *
     * @param       array $parameters
     */
    public function initialize(array $parameters = array());

    /**
     * Process the given input and return a corresponding deterministic output.
     *
     * @param       array $input
     *
     * @return      array
     */
    public function execute(array $input);
}
